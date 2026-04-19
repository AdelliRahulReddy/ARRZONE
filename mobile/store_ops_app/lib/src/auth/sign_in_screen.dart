import 'package:firebase_auth/firebase_auth.dart';
import 'package:flutter/material.dart';
import 'package:google_sign_in/google_sign_in.dart';
import 'package:store_ops_app/src/widgets/app_backdrop.dart';
import 'package:store_ops_app/src/widgets/section_card.dart';
import 'package:store_ops_app/src/widgets/status_banner.dart';

class SignInScreen extends StatefulWidget {
  const SignInScreen({super.key});

  @override
  State<SignInScreen> createState() => _SignInScreenState();
}

class _SignInScreenState extends State<SignInScreen> {
  final _emailController = TextEditingController();
  final _passwordController = TextEditingController();
  final _googleSignIn = GoogleSignIn();

  bool _pending = false;
  String _errorMessage = '';

  @override
  void dispose() {
    _emailController.dispose();
    _passwordController.dispose();
    super.dispose();
  }

  Future<void> _signInWithGoogle() async {
    FocusScope.of(context).unfocus();
    setState(() {
      _pending = true;
      _errorMessage = '';
    });

    try {
      final googleUser = await _googleSignIn.signIn();
      if (googleUser == null) {
        if (!mounted) {
          return;
        }
        setState(() {
          _errorMessage = 'Google sign-in was cancelled.';
        });
        return;
      }

      final googleAuth = await googleUser.authentication;
      final credential = GoogleAuthProvider.credential(
        accessToken: googleAuth.accessToken,
        idToken: googleAuth.idToken,
      );
      await FirebaseAuth.instance.signInWithCredential(credential);
    } on FirebaseAuthException catch (error) {
      setState(() {
        _errorMessage = firebaseErrorMessage(error);
      });
    } catch (error) {
      setState(() {
        _errorMessage = 'Google sign-in failed. Try again.';
      });
    } finally {
      if (mounted) {
        setState(() {
          _pending = false;
        });
      }
    }
  }

  Future<void> _signInWithEmail() async {
    FocusScope.of(context).unfocus();
    setState(() {
      _pending = true;
      _errorMessage = '';
    });

    try {
      await FirebaseAuth.instance.signInWithEmailAndPassword(
        email: _emailController.text.trim(),
        password: _passwordController.text,
      );
    } on FirebaseAuthException catch (error) {
      setState(() {
        _errorMessage = firebaseErrorMessage(error);
      });
    } catch (_) {
      setState(() {
        _errorMessage = 'Sign-in failed. Try again.';
      });
    } finally {
      if (mounted) {
        setState(() {
          _pending = false;
        });
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);

    return Scaffold(
      body: AppBackdrop(
        child: SafeArea(
          child: SingleChildScrollView(
            padding: const EdgeInsets.fromLTRB(20, 24, 20, 28),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Container(
                  width: 72,
                  height: 72,
                  decoration: BoxDecoration(
                    borderRadius: BorderRadius.circular(24),
                    gradient: const LinearGradient(
                      colors: [
                        Color(0xFF24140E),
                        Color(0xFF8E4325),
                        Color(0xFFD38B52),
                      ],
                      begin: Alignment.topLeft,
                      end: Alignment.bottomRight,
                    ),
                  ),
                  child: const Icon(
                    Icons.storefront_rounded,
                    color: Colors.white,
                    size: 34,
                  ),
                ),
                const SizedBox(height: 20),
                Text(
                  'ARRZONE Store Ops',
                  style: theme.textTheme.headlineMedium?.copyWith(
                    fontWeight: FontWeight.w800,
                  ),
                ),
                const SizedBox(height: 10),
                Text(
                  'A counter-first mobile workspace for store staff. Sign in once and go straight into your approved store tools.',
                  style: theme.textTheme.bodyLarge?.copyWith(
                    color: const Color(0xFF5F5A55),
                    height: 1.5,
                  ),
                ),
                const SizedBox(height: 24),
                const _IntroStrip(),
                const SizedBox(height: 24),
                SectionCard(
                  title: 'Sign in',
                  subtitle:
                      'Use the account already approved for your ARRZONE store role.',
                  child: Column(
                    children: [
                      SizedBox(
                        width: double.infinity,
                        child: OutlinedButton.icon(
                          onPressed: _pending ? null : _signInWithGoogle,
                          icon: const Icon(
                            Icons.g_mobiledata_rounded,
                            size: 28,
                          ),
                          label: Text(
                            _pending
                                ? 'Please wait...'
                                : 'Continue with Google',
                          ),
                        ),
                      ),
                      const SizedBox(height: 18),
                      Row(
                        children: [
                          Expanded(
                            child: Divider(
                              color: Theme.of(
                                context,
                              ).colorScheme.outlineVariant,
                            ),
                          ),
                          Padding(
                            padding: const EdgeInsets.symmetric(horizontal: 12),
                            child: Text(
                              'or',
                              style: theme.textTheme.bodySmall?.copyWith(
                                color: const Color(0xFF6C7483),
                                fontWeight: FontWeight.w600,
                              ),
                            ),
                          ),
                          Expanded(
                            child: Divider(
                              color: Theme.of(
                                context,
                              ).colorScheme.outlineVariant,
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 18),
                      TextField(
                        controller: _emailController,
                        keyboardType: TextInputType.emailAddress,
                        decoration: const InputDecoration(
                          labelText: 'Work email',
                          hintText: 'operator@arrzone.com',
                        ),
                      ),
                      const SizedBox(height: 14),
                      TextField(
                        controller: _passwordController,
                        obscureText: true,
                        onSubmitted: (_) =>
                            _pending ? null : _signInWithEmail(),
                        decoration: const InputDecoration(
                          labelText: 'Password',
                        ),
                      ),
                      const SizedBox(height: 18),
                      SizedBox(
                        width: double.infinity,
                        child: FilledButton.icon(
                          onPressed: _pending ? null : _signInWithEmail,
                          icon: _pending
                              ? const SizedBox(
                                  width: 18,
                                  height: 18,
                                  child: CircularProgressIndicator(
                                    strokeWidth: 2,
                                  ),
                                )
                              : const Icon(Icons.arrow_forward_rounded),
                          label: Text(
                            _pending ? 'Signing in...' : 'Open workspace',
                          ),
                        ),
                      ),
                    ],
                  ),
                ),
                if (_errorMessage.isNotEmpty) ...[
                  const SizedBox(height: 18),
                  StatusBanner(
                    title: 'Sign-in failed',
                    message: _errorMessage,
                    color: const Color(0xFF9F2D2D),
                    icon: Icons.error_outline_rounded,
                  ),
                ],
              ],
            ),
          ),
        ),
      ),
    );
  }
}

class _IntroStrip extends StatelessWidget {
  const _IntroStrip();

  @override
  Widget build(BuildContext context) {
    return Row(
      children: const [
        Expanded(
          child: _IntroPill(
            icon: Icons.qr_code_scanner_rounded,
            label: 'Scan fast',
          ),
        ),
        SizedBox(width: 12),
        Expanded(
          child: _IntroPill(
            icon: Icons.payments_outlined,
            label: 'Take action',
          ),
        ),
        SizedBox(width: 12),
        Expanded(
          child: _IntroPill(
            icon: Icons.store_mall_directory_outlined,
            label: 'Stay scoped',
          ),
        ),
      ],
    );
  }
}

class _IntroPill extends StatelessWidget {
  const _IntroPill({required this.icon, required this.label});

  final IconData icon;
  final String label;

  @override
  Widget build(BuildContext context) {
    return DecoratedBox(
      decoration: BoxDecoration(
        color: Colors.white.withValues(alpha: 0.9),
        borderRadius: BorderRadius.circular(18),
        border: Border.all(color: Theme.of(context).colorScheme.outlineVariant),
      ),
      child: Padding(
        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 14),
        child: Column(
          children: [
            Icon(icon, color: const Color(0xFF8E4325)),
            const SizedBox(height: 8),
            Text(
              label,
              style: Theme.of(
                context,
              ).textTheme.labelLarge?.copyWith(fontWeight: FontWeight.w700),
            ),
          ],
        ),
      ),
    );
  }
}

String firebaseErrorMessage(FirebaseAuthException error) {
  switch (error.code) {
    case 'invalid-credential':
      return 'The email or password is incorrect.';
    case 'invalid-email':
      return 'Enter a valid email address.';
    case 'user-disabled':
      return 'This account is disabled.';
    case 'network-request-failed':
      return 'Network error while contacting Firebase.';
    case 'too-many-requests':
      return 'Too many attempts. Wait a moment and try again.';
    case 'account-exists-with-different-credential':
      return 'This email already exists with another sign-in method.';
    default:
      return error.message ?? 'Firebase sign-in failed.';
  }
}
