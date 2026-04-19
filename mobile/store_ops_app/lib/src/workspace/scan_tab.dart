import 'package:firebase_auth/firebase_auth.dart';
import 'package:flutter/material.dart';
import 'package:mobile_scanner/mobile_scanner.dart';
import 'package:store_ops_app/src/services/mobile_bootstrap_service.dart';
import 'package:store_ops_app/src/services/staff_lookup_service.dart';
import 'package:store_ops_app/src/widgets/section_card.dart';
import 'package:store_ops_app/src/widgets/status_banner.dart';

class ScanTab extends StatefulWidget {
  const ScanTab({super.key, required this.actor, required this.user});

  final MobileActor actor;
  final User user;

  @override
  State<ScanTab> createState() => _ScanTabState();
}

class _ScanTabState extends State<ScanTab> with AutomaticKeepAliveClientMixin {
  final _scannerController = MobileScannerController(
    detectionSpeed: DetectionSpeed.noDuplicates,
    facing: CameraFacing.back,
    torchEnabled: false,
  );
  final _lookupService = const StaffLookupService();

  MemberLookupResult? _member;
  String _status = 'Point the camera at a member pass QR to load the account.';
  String _errorMessage = '';
  bool _busy = false;
  bool _scanLocked = false;
  bool _torchEnabled = false;

  @override
  bool get wantKeepAlive => true;

  @override
  void dispose() {
    _scannerController.dispose();
    super.dispose();
  }

  Future<void> _handleDetect(BarcodeCapture capture) async {
    if (_busy || _scanLocked) {
      return;
    }

    final rawValue = capture.barcodes
        .map((barcode) => barcode.rawValue?.trim() ?? '')
        .firstWhere((value) => value.isNotEmpty, orElse: () => '');
    if (rawValue.isEmpty) {
      return;
    }

    setState(() {
      _busy = true;
      _scanLocked = true;
      _errorMessage = '';
      _status = 'Looking up member access...';
    });

    try {
      await _scannerController.stop();
      final idToken = await widget.user.getIdToken(true);
      if (idToken == null || idToken.isEmpty) {
        throw const ApiException('Please sign in again.');
      }

      final result = await _lookupService.lookupByQr(
        idToken: idToken,
        qrPayload: rawValue,
      );

      if (!mounted) {
        return;
      }

      setState(() {
        _member = result;
        _status =
            'Member loaded. You can continue with purchase or redemption.';
      });
    } on ApiException catch (error) {
      if (!mounted) {
        return;
      }

      setState(() {
        _member = null;
        _errorMessage = error.message;
        _status = 'This QR could not be used for member lookup.';
      });
    } catch (_) {
      if (!mounted) {
        return;
      }

      setState(() {
        _member = null;
        _errorMessage = 'Scanner lookup failed. Try again.';
        _status = 'Scanner lookup failed.';
      });
    } finally {
      if (mounted) {
        setState(() {
          _busy = false;
        });
      }
    }
  }

  Future<void> resumeScanning() async {
    setState(() {
      _scanLocked = false;
      _member = null;
      _errorMessage = '';
      _status = 'Point the camera at a member pass QR to load the account.';
    });
    await _scannerController.start();
  }

  Future<void> _toggleTorch() async {
    await _scannerController.toggleTorch();
    if (!mounted) {
      return;
    }

    setState(() {
      _torchEnabled = !_torchEnabled;
    });
  }

  @override
  Widget build(BuildContext context) {
    super.build(context);
    final accent = Theme.of(context).colorScheme.primary;

    return ListView(
      padding: const EdgeInsets.fromLTRB(20, 12, 20, 32),
      children: [
        DecoratedBox(
          decoration: BoxDecoration(
            gradient: const LinearGradient(
              colors: [Color(0xFF23150E), Color(0xFF6A3320), Color(0xFFB86436)],
              begin: Alignment.topLeft,
              end: Alignment.bottomRight,
            ),
            borderRadius: BorderRadius.circular(30),
          ),
          child: Padding(
            padding: const EdgeInsets.all(18),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  'Live scanner',
                  style: Theme.of(context).textTheme.headlineSmall?.copyWith(
                    color: Colors.white,
                    fontWeight: FontWeight.w800,
                  ),
                ),
                const SizedBox(height: 8),
                Text(
                  'Built for counter staff. Scan a member pass and stay in one fast mobile flow.',
                  style: Theme.of(context).textTheme.bodyLarge?.copyWith(
                    color: const Color(0xFFF7E8DA),
                    height: 1.45,
                  ),
                ),
                const SizedBox(height: 18),
                ClipRRect(
                  borderRadius: BorderRadius.circular(26),
                  child: AspectRatio(
                    aspectRatio: 0.82,
                    child: Stack(
                      fit: StackFit.expand,
                      children: [
                        MobileScanner(
                          controller: _scannerController,
                          fit: BoxFit.cover,
                          onDetect: _handleDetect,
                          errorBuilder: (context, error) {
                            return _ScannerErrorState(
                              message: _scannerErrorMessage(error),
                              onRetry: resumeScanning,
                            );
                          },
                        ),
                        const _ScannerMask(),
                        Positioned(
                          top: 14,
                          left: 14,
                          right: 14,
                          child: Row(
                            children: [
                              _OverlayChip(
                                icon: Icons.center_focus_strong_rounded,
                                label: _busy ? 'Processing' : 'Ready',
                              ),
                              const Spacer(),
                              _OverlayIconButton(
                                icon: _torchEnabled
                                    ? Icons.flash_on_rounded
                                    : Icons.flash_off_rounded,
                                onPressed: _toggleTorch,
                              ),
                            ],
                          ),
                        ),
                        Positioned(
                          left: 14,
                          right: 14,
                          bottom: 14,
                          child: DecoratedBox(
                            decoration: BoxDecoration(
                              color: Colors.black.withValues(alpha: 0.52),
                              borderRadius: BorderRadius.circular(18),
                            ),
                            child: Padding(
                              padding: const EdgeInsets.all(14),
                              child: Text(
                                _status,
                                style: Theme.of(context).textTheme.bodyMedium
                                    ?.copyWith(
                                      color: Colors.white,
                                      height: 1.4,
                                    ),
                              ),
                            ),
                          ),
                        ),
                      ],
                    ),
                  ),
                ),
                const SizedBox(height: 14),
                Row(
                  children: [
                    Expanded(
                      child: FilledButton.icon(
                        onPressed: _busy ? null : resumeScanning,
                        icon: const Icon(Icons.qr_code_scanner_rounded),
                        label: const Text('Scan again'),
                      ),
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: OutlinedButton.icon(
                        onPressed: _busy ? null : _toggleTorch,
                        icon: Icon(
                          _torchEnabled
                              ? Icons.flash_on_rounded
                              : Icons.flash_off_rounded,
                        ),
                        label: const Text('Torch'),
                      ),
                    ),
                  ],
                ),
              ],
            ),
          ),
        ),
        const SizedBox(height: 18),
        if (_errorMessage.isNotEmpty)
          StatusBanner(
            title: 'Scan failed',
            message: _errorMessage,
            color: const Color(0xFF9F2D2D),
            icon: Icons.error_outline_rounded,
          )
        else if (_member != null)
          _MemberResultCard(member: _member!)
        else
          SectionCard(
            title: 'Ready for lookup',
            subtitle:
                'Cashiers and managers should start here. Admin-only backend details do not belong in this flow.',
            child: Column(
              children: [
                _InfoRow(
                  icon: Icons.storefront_outlined,
                  label: 'Store scope',
                  value: widget.actor.branchSummary,
                ),
                const SizedBox(height: 12),
                _InfoRow(
                  icon: Icons.badge_outlined,
                  label: 'Signed in as',
                  value: widget.actor.roleLabel,
                ),
                const SizedBox(height: 12),
                _InfoRow(
                  icon: Icons.verified_user_outlined,
                  label: 'Next step',
                  value: 'Scan a pass QR to load the member account.',
                ),
              ],
            ),
          ),
        const SizedBox(height: 18),
        SectionCard(
          title: 'Scan flow',
          subtitle:
              'Once a member is loaded, this screen should hand off to the rest of the counter journey.',
          child: Wrap(
            spacing: 12,
            runSpacing: 12,
            children: [
              _ActionStub(
                icon: Icons.receipt_long_rounded,
                title: 'Add purchase',
                caption:
                    'Move straight into the earning flow after a successful lookup.',
                accent: accent,
              ),
              _ActionStub(
                icon: Icons.redeem_rounded,
                title: 'Redeem reward',
                caption:
                    'Use the same member context without forcing another lookup.',
                accent: accent,
              ),
            ],
          ),
        ),
      ],
    );
  }
}

String _scannerErrorMessage(MobileScannerException error) {
  switch (error.errorCode) {
    case MobileScannerErrorCode.permissionDenied:
      return 'Camera permission is required to scan member passes. Allow camera access in Android settings and reopen the scanner.';
    case MobileScannerErrorCode.unsupported:
      return 'This device does not support camera scanning.';
    default:
      return error.errorDetails?.message ?? error.errorCode.message;
  }
}

class _MemberResultCard extends StatelessWidget {
  const _MemberResultCard({required this.member});

  final MemberLookupResult member;

  @override
  Widget build(BuildContext context) {
    return SectionCard(
      title: member.customerName,
      subtitle:
          '${member.planName} • ${member.branchName.isEmpty ? member.branchId : member.branchName}',
      child: Column(
        children: [
          Row(
            children: [
              Expanded(
                child: _MetricCard(
                  label: 'Purchases',
                  value: member.purchaseCount.toString(),
                  accent: const Color(0xFF214C75),
                ),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: _MetricCard(
                  label: 'Rewards',
                  value: member.rewardBalance.toString(),
                  accent: const Color(0xFF8F4A20),
                ),
              ),
            ],
          ),
          const SizedBox(height: 14),
          _InfoRow(
            icon: Icons.phone_outlined,
            label: 'Phone',
            value: member.maskedPhone,
          ),
          const SizedBox(height: 12),
          _InfoRow(
            icon: Icons.badge_outlined,
            label: 'Membership',
            value: member.membershipId,
          ),
          const SizedBox(height: 12),
          _InfoRow(
            icon: Icons.check_circle_outline_rounded,
            label: 'Status',
            value: member.status,
          ),
        ],
      ),
    );
  }
}

class _MetricCard extends StatelessWidget {
  const _MetricCard({
    required this.label,
    required this.value,
    required this.accent,
  });

  final String label;
  final String value;
  final Color accent;

  @override
  Widget build(BuildContext context) {
    return DecoratedBox(
      decoration: BoxDecoration(
        color: accent.withValues(alpha: 0.08),
        borderRadius: BorderRadius.circular(22),
        border: Border.all(color: accent.withValues(alpha: 0.18)),
      ),
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              label.toUpperCase(),
              style: Theme.of(context).textTheme.labelSmall?.copyWith(
                letterSpacing: 1.1,
                color: accent,
                fontWeight: FontWeight.w700,
              ),
            ),
            const SizedBox(height: 8),
            Text(
              value,
              style: Theme.of(
                context,
              ).textTheme.headlineSmall?.copyWith(fontWeight: FontWeight.w800),
            ),
          ],
        ),
      ),
    );
  }
}

class _InfoRow extends StatelessWidget {
  const _InfoRow({
    required this.icon,
    required this.label,
    required this.value,
  });

  final IconData icon;
  final String label;
  final String value;

  @override
  Widget build(BuildContext context) {
    return Row(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        DecoratedBox(
          decoration: BoxDecoration(
            color: const Color(0xFFF0E1D3),
            borderRadius: BorderRadius.circular(14),
          ),
          child: Padding(
            padding: const EdgeInsets.all(10),
            child: Icon(icon, color: const Color(0xFF8F4A20)),
          ),
        ),
        const SizedBox(width: 12),
        Expanded(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                label.toUpperCase(),
                style: Theme.of(context).textTheme.labelSmall?.copyWith(
                  letterSpacing: 1.1,
                  color: const Color(0xFF74675F),
                ),
              ),
              const SizedBox(height: 4),
              Text(
                value,
                style: Theme.of(context).textTheme.bodyLarge?.copyWith(
                  fontWeight: FontWeight.w600,
                  height: 1.4,
                ),
              ),
            ],
          ),
        ),
      ],
    );
  }
}

class _ActionStub extends StatelessWidget {
  const _ActionStub({
    required this.icon,
    required this.title,
    required this.caption,
    required this.accent,
  });

  final IconData icon;
  final String title;
  final String caption;
  final Color accent;

  @override
  Widget build(BuildContext context) {
    return SizedBox(
      width: 220,
      child: DecoratedBox(
        decoration: BoxDecoration(
          color: Colors.white,
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
              Icon(icon, color: accent),
              const SizedBox(height: 10),
              Text(
                title,
                style: Theme.of(
                  context,
                ).textTheme.titleMedium?.copyWith(fontWeight: FontWeight.w700),
              ),
              const SizedBox(height: 6),
              Text(
                caption,
                style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                  color: const Color(0xFF616977),
                  height: 1.45,
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

class _OverlayChip extends StatelessWidget {
  const _OverlayChip({required this.icon, required this.label});

  final IconData icon;
  final String label;

  @override
  Widget build(BuildContext context) {
    return DecoratedBox(
      decoration: BoxDecoration(
        color: Colors.black.withValues(alpha: 0.48),
        borderRadius: BorderRadius.circular(999),
      ),
      child: Padding(
        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
        child: Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            Icon(icon, size: 16, color: Colors.white),
            const SizedBox(width: 8),
            Text(
              label,
              style: Theme.of(context).textTheme.labelMedium?.copyWith(
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

class _OverlayIconButton extends StatelessWidget {
  const _OverlayIconButton({required this.icon, required this.onPressed});

  final IconData icon;
  final VoidCallback onPressed;

  @override
  Widget build(BuildContext context) {
    return Material(
      color: Colors.black.withValues(alpha: 0.48),
      borderRadius: BorderRadius.circular(16),
      child: InkWell(
        onTap: onPressed,
        borderRadius: BorderRadius.circular(16),
        child: Padding(
          padding: const EdgeInsets.all(10),
          child: Icon(icon, color: Colors.white),
        ),
      ),
    );
  }
}

class _ScannerMask extends StatelessWidget {
  const _ScannerMask();

  @override
  Widget build(BuildContext context) {
    return IgnorePointer(child: CustomPaint(painter: _ScannerMaskPainter()));
  }
}

class _ScannerMaskPainter extends CustomPainter {
  @override
  void paint(Canvas canvas, Size size) {
    final overlayPaint = Paint()..color = Colors.black.withValues(alpha: 0.32);
    final hole = RRect.fromRectAndRadius(
      Rect.fromCenter(
        center: Offset(size.width / 2, size.height / 2),
        width: size.width * 0.68,
        height: size.width * 0.68,
      ),
      const Radius.circular(28),
    );

    final full = Path()..addRect(Rect.fromLTWH(0, 0, size.width, size.height));
    final cutout = Path()..addRRect(hole);
    final mask = Path.combine(PathOperation.difference, full, cutout);
    canvas.drawPath(mask, overlayPaint);

    final borderPaint = Paint()
      ..color = Colors.white.withValues(alpha: 0.92)
      ..style = PaintingStyle.stroke
      ..strokeWidth = 3;
    canvas.drawRRect(hole, borderPaint);
  }

  @override
  bool shouldRepaint(covariant CustomPainter oldDelegate) => false;
}

class _ScannerErrorState extends StatelessWidget {
  const _ScannerErrorState({required this.message, required this.onRetry});

  final String message;
  final Future<void> Function() onRetry;

  @override
  Widget build(BuildContext context) {
    return DecoratedBox(
      decoration: const BoxDecoration(color: Color(0xFF1D120E)),
      child: Padding(
        padding: const EdgeInsets.all(24),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Icon(
              Icons.videocam_off_rounded,
              color: Color(0xFFF6E7DA),
              size: 34,
            ),
            const SizedBox(height: 14),
            Text(
              'Scanner unavailable',
              style: Theme.of(context).textTheme.headlineSmall?.copyWith(
                color: Colors.white,
                fontWeight: FontWeight.w800,
              ),
            ),
            const SizedBox(height: 10),
            Text(
              message,
              style: Theme.of(context).textTheme.bodyLarge?.copyWith(
                color: const Color(0xFFF7E8DA),
                height: 1.45,
              ),
            ),
            const SizedBox(height: 18),
            OutlinedButton.icon(
              onPressed: onRetry,
              icon: const Icon(Icons.refresh_rounded),
              label: const Text('Retry scanner'),
            ),
          ],
        ),
      ),
    );
  }
}
