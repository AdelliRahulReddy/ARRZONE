import 'package:firebase_auth/firebase_auth.dart';
import 'package:flutter/material.dart';
import 'package:store_ops_app/src/auth/sign_in_screen.dart';
import 'package:store_ops_app/src/services/mobile_bootstrap_service.dart';
import 'package:store_ops_app/src/widgets/app_backdrop.dart';
import 'package:store_ops_app/src/widgets/section_card.dart';
import 'package:store_ops_app/src/widgets/status_banner.dart';
import 'package:store_ops_app/src/workspace/workspace_shell.dart';

class WorkspaceGate extends StatefulWidget {
  const WorkspaceGate({super.key, required this.user});

  final User user;

  @override
  State<WorkspaceGate> createState() => _WorkspaceGateState();
}

class _WorkspaceGateState extends State<WorkspaceGate> {
  final _service = const MobileBootstrapService();

  MobileActor? _actor;
  bool _pending = false;
  String _errorMessage = '';

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      _resolveActor(forceRefreshToken: true);
    });
  }

  @override
  void didUpdateWidget(covariant WorkspaceGate oldWidget) {
    super.didUpdateWidget(oldWidget);
    if (widget.user.uid != oldWidget.user.uid) {
      WidgetsBinding.instance.addPostFrameCallback((_) {
        _resolveActor(forceRefreshToken: true);
      });
    }
  }

  Future<void> _resolveActor({bool forceRefreshToken = false}) async {
    setState(() {
      _pending = true;
      _errorMessage = '';
    });

    try {
      final idToken = await widget.user.getIdToken(forceRefreshToken);
      if (idToken == null || idToken.isEmpty) {
        throw const ApiException('Please sign in again.');
      }
      final actor = await _service.resolveActor(idToken: idToken);
      if (!mounted) {
        return;
      }
      setState(() {
        _actor = actor;
      });
    } on FirebaseAuthException catch (error) {
      setState(() {
        _actor = null;
        _errorMessage = firebaseErrorMessage(error);
      });
    } on ApiException catch (error) {
      setState(() {
        _actor = null;
        _errorMessage = error.message;
      });
    } catch (_) {
      setState(() {
        _actor = null;
        _errorMessage = 'We could not open your workspace yet.';
      });
    } finally {
      if (mounted) {
        setState(() {
          _pending = false;
        });
      }
    }
  }

  Future<void> _signOut() async {
    await FirebaseAuth.instance.signOut();
  }

  @override
  Widget build(BuildContext context) {
    if (_pending && _actor == null) {
      return Scaffold(
        body: AppBackdrop(
          child: SafeArea(
            child: Padding(
              padding: const EdgeInsets.all(20),
              child: SectionCard(
                title: 'Opening your workspace',
                subtitle:
                    'Checking your role and store access before we load the right mobile tools.',
                child: const Row(
                  children: [
                    SizedBox(
                      width: 24,
                      height: 24,
                      child: CircularProgressIndicator(strokeWidth: 2.5),
                    ),
                    SizedBox(width: 14),
                    Expanded(
                      child: Text(
                        'This should take only a moment after sign-in.',
                      ),
                    ),
                  ],
                ),
              ),
            ),
          ),
        ),
      );
    }

    if (_actor != null) {
      return WorkspaceShell(
        actor: _actor!,
        user: widget.user,
        onRefresh: () => _resolveActor(forceRefreshToken: true),
        onSignOut: _signOut,
      );
    }

    return Scaffold(
      body: AppBackdrop(
        child: SafeArea(
          child: Padding(
            padding: const EdgeInsets.all(20),
            child: Column(
              children: [
                StatusBanner(
                  title: 'Workspace unavailable',
                  message: _errorMessage.isEmpty
                      ? 'We could not load your approved store access.'
                      : _errorMessage,
                  color: const Color(0xFF9F2D2D),
                  icon: Icons.error_outline_rounded,
                ),
                const SizedBox(height: 18),
                SectionCard(
                  title: 'What this means',
                  subtitle:
                      'Your Firebase sign-in worked, but this account still needs valid ARRZONE access for the right role and store.',
                  child: Row(
                    children: [
                      Expanded(
                        child: OutlinedButton.icon(
                          onPressed: _pending
                              ? null
                              : () => _resolveActor(forceRefreshToken: true),
                          icon: const Icon(Icons.refresh_rounded),
                          label: const Text('Retry'),
                        ),
                      ),
                      const SizedBox(width: 12),
                      Expanded(
                        child: FilledButton.icon(
                          onPressed: _signOut,
                          icon: const Icon(Icons.logout_rounded),
                          label: const Text('Sign out'),
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }
}
