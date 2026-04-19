import 'package:firebase_auth/firebase_auth.dart';
import 'package:flutter/material.dart';
import 'package:google_sign_in/google_sign_in.dart';
import 'package:store_ops_app/src/services/mobile_bootstrap_service.dart';
import 'package:store_ops_app/src/widgets/app_backdrop.dart';
import 'package:store_ops_app/src/widgets/section_card.dart';
import 'package:store_ops_app/src/workspace/scan_tab.dart';

class WorkspaceShell extends StatefulWidget {
  const WorkspaceShell({
    super.key,
    required this.actor,
    required this.user,
    required this.onRefresh,
    required this.onSignOut,
  });

  final MobileActor actor;
  final User user;
  final Future<void> Function() onRefresh;
  final Future<void> Function() onSignOut;

  @override
  State<WorkspaceShell> createState() => _WorkspaceShellState();
}

class _WorkspaceShellState extends State<WorkspaceShell> {
  int _currentIndex = 0;
  bool _actionPending = false;

  late final List<_NavItem> _items = _buildItems(widget.actor);

  @override
  Widget build(BuildContext context) {
    final current = _items[_currentIndex];

    return Scaffold(
      appBar: AppBar(
        title: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(widget.actor.workspaceTitle),
            Text(
              widget.actor.roleLabel,
              style: Theme.of(
                context,
              ).textTheme.labelMedium?.copyWith(color: const Color(0xFF6C7483)),
            ),
          ],
        ),
      ),
      body: AppBackdrop(
        child: SafeArea(
          top: false,
          child: AnimatedSwitcher(
            duration: const Duration(milliseconds: 220),
            child: KeyedSubtree(
              key: ValueKey<String>(current.label),
              child: current.builder(context),
            ),
          ),
        ),
      ),
      bottomNavigationBar: NavigationBar(
        selectedIndex: _currentIndex,
        destinations: _items
            .map(
              (item) => NavigationDestination(
                icon: Icon(item.icon),
                label: item.label,
              ),
            )
            .toList(),
        onDestinationSelected: (index) {
          setState(() {
            _currentIndex = index;
          });
        },
      ),
      floatingActionButton: _currentIndex == 0 && !widget.actor.isPlatformAdmin
          ? FloatingActionButton.extended(
              onPressed: () {
                final scanIndex = _items.indexWhere(
                  (item) => item.label == 'Scan',
                );
                if (scanIndex >= 0) {
                  setState(() {
                    _currentIndex = scanIndex;
                  });
                }
              },
              icon: const Icon(Icons.qr_code_scanner_rounded),
              label: const Text('Scan'),
            )
          : null,
    );
  }

  void _openTab(String label) {
    final nextIndex = _items.indexWhere((item) => item.label == label);
    if (nextIndex < 0 || nextIndex == _currentIndex) {
      return;
    }

    setState(() {
      _currentIndex = nextIndex;
    });
  }

  List<_NavItem> _buildItems(MobileActor actor) {
    if (actor.isPlatformAdmin) {
      return [
        _NavItem(
          label: 'Overview',
          icon: Icons.space_dashboard_outlined,
          builder: (_) => _PlatformOverviewTab(actor: actor, user: widget.user),
        ),
        _NavItem(
          label: 'Tenants',
          icon: Icons.apartment_rounded,
          builder: (_) => _PlaceholderTab(
            title: 'Tenant management',
            subtitle:
                'Tenant provisioning and health views should live here for platform admins.',
          ),
        ),
        _NavItem(
          label: 'Security',
          icon: Icons.shield_outlined,
          builder: (_) => _PlaceholderTab(
            title: 'Security review',
            subtitle:
                'Platform exception review and risk signals should be visible in this tab.',
          ),
        ),
        _NavItem(
          label: 'Account',
          icon: Icons.person_outline_rounded,
          builder: (_) => _AccountTab(
            actor: actor,
            user: widget.user,
            actionPending: _actionPending,
            onRefresh: _handleRefresh,
            onSignOut: _handleSignOut,
          ),
        ),
      ];
    }

    final items = <_NavItem>[
      _NavItem(
        label: 'Home',
        icon: Icons.home_outlined,
        builder: (_) => _StoreHomeTab(
          actor: actor,
          user: widget.user,
          onOpenScan: () => _openTab('Scan'),
          onOpenMembers: () => _openTab('Members'),
          onOpenReview: () => _openTab('Review'),
          onOpenAdmin: () => _openTab('Admin'),
        ),
      ),
      _NavItem(
        label: 'Members',
        icon: Icons.people_outline_rounded,
        builder: (_) => _MembersTab(actor: actor),
      ),
      _NavItem(
        label: 'Scan',
        icon: Icons.qr_code_scanner_rounded,
        builder: (_) => ScanTab(actor: actor, user: widget.user),
      ),
      _NavItem(
        label: 'Account',
        icon: Icons.person_outline_rounded,
        builder: (_) => _AccountTab(
          actor: actor,
          user: widget.user,
          actionPending: _actionPending,
          onRefresh: _handleRefresh,
          onSignOut: _handleSignOut,
        ),
      ),
    ];

    if (actor.isManager || actor.isBusinessAdmin) {
      items.insert(
        3,
        _NavItem(
          label: 'Review',
          icon: Icons.fact_check_outlined,
          builder: (_) => _ReviewTab(actor: actor),
        ),
      );
    }

    if (actor.isBusinessAdmin) {
      items.insert(
        items.length - 1,
        _NavItem(
          label: 'Admin',
          icon: Icons.admin_panel_settings_outlined,
          builder: (_) => _AdminTab(actor: actor),
        ),
      );
    }

    return items;
  }

  Future<void> _handleRefresh() async {
    setState(() {
      _actionPending = true;
    });
    try {
      await widget.onRefresh();
    } finally {
      if (mounted) {
        setState(() {
          _actionPending = false;
        });
      }
    }
  }

  Future<void> _handleSignOut() async {
    setState(() {
      _actionPending = true;
    });
    try {
      await GoogleSignIn().signOut();
      await widget.onSignOut();
    } finally {
      if (mounted) {
        setState(() {
          _actionPending = false;
        });
      }
    }
  }
}

class _NavItem {
  const _NavItem({
    required this.label,
    required this.icon,
    required this.builder,
  });

  final String label;
  final IconData icon;
  final WidgetBuilder builder;
}

class _StoreHomeTab extends StatelessWidget {
  const _StoreHomeTab({
    required this.actor,
    required this.user,
    required this.onOpenScan,
    required this.onOpenMembers,
    required this.onOpenReview,
    required this.onOpenAdmin,
  });

  final MobileActor actor;
  final User user;
  final VoidCallback onOpenScan;
  final VoidCallback onOpenMembers;
  final VoidCallback onOpenReview;
  final VoidCallback onOpenAdmin;

  @override
  Widget build(BuildContext context) {
    return ListView(
      padding: const EdgeInsets.fromLTRB(20, 12, 20, 32),
      children: [
        _HeroCard(
          eyebrow: actor.roleLabel,
          title: actor.workspaceTitle,
          subtitle: actor.workspaceSubtitle,
          chips: [
            _HeroChip(
              label: actor.branchSummary,
              icon: Icons.storefront_outlined,
            ),
            _HeroChip(
              label: user.email ?? 'Signed in',
              icon: Icons.badge_outlined,
            ),
          ],
        ),
        const SizedBox(height: 18),
        SectionCard(
          title: 'Quick actions',
          subtitle:
              'The main counter flow should start here. Cashiers go straight to store work, not backend state.',
          child: Wrap(
            spacing: 12,
            runSpacing: 12,
            children: [
              _ActionTile(
                icon: Icons.qr_code_scanner_rounded,
                title: 'Scan member QR',
                caption:
                    'Start with camera-based lookup and handoff to purchase or redeem.',
                onTap: onOpenScan,
              ),
              _ActionTile(
                icon: Icons.search_rounded,
                title: 'Find by phone',
                caption:
                    'Fallback lookup when the member cannot present the QR right away.',
                onTap: onOpenMembers,
              ),
              _ActionTile(
                icon: Icons.receipt_long_rounded,
                title: 'Add purchase',
                caption:
                    'Record an earning event quickly after the member is identified.',
                onTap: onOpenScan,
              ),
              _ActionTile(
                icon: Icons.redeem_rounded,
                title: 'Redeem reward',
                caption:
                    'Use live server validation before approving a redemption.',
                onTap: actor.isManager || actor.isBusinessAdmin
                    ? onOpenReview
                    : onOpenScan,
              ),
              if (actor.isBusinessAdmin)
                _ActionTile(
                  icon: Icons.admin_panel_settings_outlined,
                  title: 'Staff and branch admin',
                  caption:
                      'Open merchant admin controls for staffing, branches, and plans.',
                  onTap: onOpenAdmin,
                ),
            ],
          ),
        ),
        const SizedBox(height: 18),
        SectionCard(
          title: 'Assigned stores',
          subtitle:
              'Staff should see their actual store scope first. Admin infrastructure details stay out of this view.',
          child: Column(
            children: actor.displayBranches
                .map(
                  (branch) =>
                      _ListRow(icon: Icons.place_outlined, text: branch),
                )
                .toList(),
          ),
        ),
      ],
    );
  }
}

class _MembersTab extends StatelessWidget {
  const _MembersTab({required this.actor});

  final MobileActor actor;

  @override
  Widget build(BuildContext context) {
    return ListView(
      padding: const EdgeInsets.fromLTRB(20, 12, 20, 32),
      children: const [
        SectionCard(
          title: 'Member workspace',
          subtitle:
              'This tab is where search, active member context, and member history should live on mobile.',
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              _ListRow(
                icon: Icons.phone_outlined,
                text: 'Search by phone or membership reference',
              ),
              SizedBox(height: 12),
              _ListRow(
                icon: Icons.card_membership_outlined,
                text: 'Show current points, rewards, and pass status',
              ),
              SizedBox(height: 12),
              _ListRow(
                icon: Icons.history_rounded,
                text: 'Open recent ledger activity without leaving the counter',
              ),
            ],
          ),
        ),
      ],
    );
  }
}

class _ReviewTab extends StatelessWidget {
  const _ReviewTab({required this.actor});

  final MobileActor actor;

  @override
  Widget build(BuildContext context) {
    return const _PlaceholderTab(
      title: 'Manager review',
      subtitle:
          'Corrections, reversals, recovery redemption, and exception review should appear here for managers and business admins.',
    );
  }
}

class _AdminTab extends StatelessWidget {
  const _AdminTab({required this.actor});

  final MobileActor actor;

  @override
  Widget build(BuildContext context) {
    return const _PlaceholderTab(
      title: 'Business admin',
      subtitle:
          'Staff management, branch setup, and plan controls belong here for merchant admins only.',
    );
  }
}

class _PlatformOverviewTab extends StatelessWidget {
  const _PlatformOverviewTab({required this.actor, required this.user});

  final MobileActor actor;
  final User user;

  @override
  Widget build(BuildContext context) {
    return ListView(
      padding: const EdgeInsets.fromLTRB(20, 12, 20, 32),
      children: [
        _HeroCard(
          eyebrow: actor.roleLabel,
          title: 'Platform operations',
          subtitle:
              'Only platform admins see cross-tenant controls and broader account visibility.',
          chips: [
            const _HeroChip(
              label: 'Cross-tenant access',
              icon: Icons.public_rounded,
            ),
            _HeroChip(
              label: user.email ?? 'Signed in',
              icon: Icons.badge_outlined,
            ),
          ],
        ),
        const SizedBox(height: 18),
        SectionCard(
          title: 'Platform visibility',
          subtitle:
              'This level of information is intentionally reserved for platform administrators.',
          child: Column(
            children: [
              _ListRow(
                icon: Icons.apartment_rounded,
                text: 'Tenant directory and platform-wide provisioning',
              ),
              const SizedBox(height: 12),
              _ListRow(
                icon: Icons.shield_outlined,
                text: 'Security event review across every merchant',
              ),
              const SizedBox(height: 12),
              _ListRow(
                icon: Icons.admin_panel_settings_outlined,
                text: 'Admin user oversight and support escalation',
              ),
            ],
          ),
        ),
      ],
    );
  }
}

class _AccountTab extends StatelessWidget {
  const _AccountTab({
    required this.actor,
    required this.user,
    required this.actionPending,
    required this.onRefresh,
    required this.onSignOut,
  });

  final MobileActor actor;
  final User user;
  final bool actionPending;
  final Future<void> Function() onRefresh;
  final Future<void> Function() onSignOut;

  @override
  Widget build(BuildContext context) {
    return ListView(
      padding: const EdgeInsets.fromLTRB(20, 12, 20, 32),
      children: [
        SectionCard(
          title: 'Account',
          subtitle:
              'Operator profile and session controls. This stays user-facing and avoids backend implementation details.',
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              _ListRow(
                icon: Icons.person_outline_rounded,
                text: user.email ?? 'Signed in account',
              ),
              const SizedBox(height: 12),
              _ListRow(icon: Icons.badge_outlined, text: actor.roleLabel),
              const SizedBox(height: 12),
              _ListRow(
                icon: Icons.storefront_outlined,
                text: actor.isPlatformAdmin
                    ? 'Platform-wide access'
                    : actor.branchSummary,
              ),
              const SizedBox(height: 18),
              Row(
                children: [
                  Expanded(
                    child: OutlinedButton.icon(
                      onPressed: actionPending ? null : onRefresh,
                      icon: const Icon(Icons.refresh_rounded),
                      label: const Text('Refresh'),
                    ),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: FilledButton.icon(
                      onPressed: actionPending ? null : onSignOut,
                      icon: const Icon(Icons.logout_rounded),
                      label: const Text('Sign out'),
                    ),
                  ),
                ],
              ),
            ],
          ),
        ),
        if (actor.isPlatformAdmin) ...[
          const SizedBox(height: 18),
          SectionCard(
            title: 'Admin visibility',
            subtitle:
                'Platform admins can review elevated information that should stay hidden from regular store staff.',
            child: Column(
              children: [
                _ListRow(
                  icon: Icons.hub_outlined,
                  text: actor.availableSurfaces.isEmpty
                      ? 'Platform surface enabled'
                      : actor.availableSurfaces.join(', '),
                ),
                const SizedBox(height: 12),
                _ListRow(
                  icon: Icons.verified_user_outlined,
                  text: 'Auth mode: ${actor.authMode}',
                ),
              ],
            ),
          ),
        ],
      ],
    );
  }
}

class _PlaceholderTab extends StatelessWidget {
  const _PlaceholderTab({required this.title, required this.subtitle});

  final String title;
  final String subtitle;

  @override
  Widget build(BuildContext context) {
    return ListView(
      padding: const EdgeInsets.fromLTRB(20, 12, 20, 32),
      children: [
        SectionCard(
          title: title,
          subtitle: subtitle,
          child: const _ListRow(
            icon: Icons.design_services_outlined,
            text:
                'This screen is reserved for the next mobile implementation slice.',
          ),
        ),
      ],
    );
  }
}

class _HeroCard extends StatelessWidget {
  const _HeroCard({
    required this.eyebrow,
    required this.title,
    required this.subtitle,
    required this.chips,
  });

  final String eyebrow;
  final String title;
  final String subtitle;
  final List<_HeroChip> chips;

  @override
  Widget build(BuildContext context) {
    return DecoratedBox(
      decoration: BoxDecoration(
        gradient: const LinearGradient(
          colors: [Color(0xFF24140E), Color(0xFF8E4325), Color(0xFFD38B52)],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
        borderRadius: BorderRadius.circular(30),
      ),
      child: Padding(
        padding: const EdgeInsets.all(24),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              eyebrow.toUpperCase(),
              style: Theme.of(context).textTheme.labelMedium?.copyWith(
                letterSpacing: 1.6,
                fontWeight: FontWeight.w700,
                color: const Color(0xFFF4E4D4),
              ),
            ),
            const SizedBox(height: 10),
            Text(
              title,
              style: Theme.of(context).textTheme.headlineSmall?.copyWith(
                fontWeight: FontWeight.w800,
                color: Colors.white,
              ),
            ),
            const SizedBox(height: 10),
            Text(
              subtitle,
              style: Theme.of(context).textTheme.bodyLarge?.copyWith(
                color: const Color(0xFFF6E7DA),
                height: 1.45,
              ),
            ),
            const SizedBox(height: 18),
            Wrap(spacing: 10, runSpacing: 10, children: chips),
          ],
        ),
      ),
    );
  }
}

class _HeroChip extends StatelessWidget {
  const _HeroChip({required this.label, required this.icon});

  final String label;
  final IconData icon;

  @override
  Widget build(BuildContext context) {
    return DecoratedBox(
      decoration: BoxDecoration(
        color: Colors.white.withValues(alpha: 0.16),
        borderRadius: BorderRadius.circular(999),
        border: Border.all(color: Colors.white.withValues(alpha: 0.18)),
      ),
      child: Padding(
        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
        child: Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            Icon(icon, size: 18, color: Colors.white),
            const SizedBox(width: 8),
            Text(
              label,
              style: Theme.of(context).textTheme.labelLarge?.copyWith(
                color: Colors.white,
                fontWeight: FontWeight.w700,
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _ActionTile extends StatelessWidget {
  const _ActionTile({
    required this.icon,
    required this.title,
    required this.caption,
    required this.onTap,
  });

  final IconData icon;
  final String title;
  final String caption;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return SizedBox(
      width: 240,
      child: Material(
        color: const Color(0xFFF9F8F4),
        borderRadius: BorderRadius.circular(22),
        child: InkWell(
          onTap: onTap,
          borderRadius: BorderRadius.circular(22),
          child: Ink(
            decoration: BoxDecoration(
              borderRadius: BorderRadius.circular(22),
              border: Border.all(
                color: Theme.of(context).colorScheme.outlineVariant,
              ),
            ),
            child: Padding(
              padding: const EdgeInsets.all(16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Icon(icon, color: Theme.of(context).colorScheme.primary),
                  const SizedBox(height: 12),
                  Text(
                    title,
                    style: Theme.of(context).textTheme.titleMedium?.copyWith(
                      fontWeight: FontWeight.w700,
                    ),
                  ),
                  const SizedBox(height: 6),
                  Text(
                    caption,
                    style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                      color: const Color(0xFF616977),
                      height: 1.45,
                    ),
                  ),
                  const SizedBox(height: 12),
                  Row(
                    children: [
                      Text(
                        'Open',
                        style: Theme.of(context).textTheme.labelLarge?.copyWith(
                          color: Theme.of(context).colorScheme.primary,
                          fontWeight: FontWeight.w700,
                        ),
                      ),
                      const SizedBox(width: 6),
                      Icon(
                        Icons.arrow_forward_rounded,
                        size: 18,
                        color: Theme.of(context).colorScheme.primary,
                      ),
                    ],
                  ),
                ],
              ),
            ),
          ),
        ),
      ),
    );
  }
}

class _ListRow extends StatelessWidget {
  const _ListRow({required this.icon, required this.text});

  final IconData icon;
  final String text;

  @override
  Widget build(BuildContext context) {
    return Row(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        DecoratedBox(
          decoration: BoxDecoration(
            color: const Color(0xFFF1E0D3),
            borderRadius: BorderRadius.circular(14),
          ),
          child: Padding(
            padding: const EdgeInsets.all(10),
            child: Icon(icon, color: const Color(0xFF8E4325)),
          ),
        ),
        const SizedBox(width: 14),
        Expanded(
          child: Padding(
            padding: const EdgeInsets.only(top: 4),
            child: Text(
              text,
              style: Theme.of(
                context,
              ).textTheme.bodyLarge?.copyWith(height: 1.45),
            ),
          ),
        ),
      ],
    );
  }
}
