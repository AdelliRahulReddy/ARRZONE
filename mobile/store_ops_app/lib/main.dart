import 'dart:convert';
import 'dart:io' show Platform;

import 'package:firebase_auth/firebase_auth.dart';
import 'package:firebase_core/firebase_core.dart';
import 'package:flutter/material.dart';
import 'package:google_sign_in/google_sign_in.dart';
import 'package:http/http.dart' as http;

const _firebaseOptions = FirebaseOptions(
  apiKey: 'AIzaSyAnxV0PGi389_WcyvFTB_5_JM8I_z0oGYI',
  appId: '1:519662663037:web:556d088d0f0b14c507c814',
  messagingSenderId: '519662663037',
  projectId: 'arrcloud-637ec',
  authDomain: 'arrcloud-637ec.firebaseapp.com',
);

const _backendBaseUrl = String.fromEnvironment(
  'ARRZONE_API_BASE_URL',
  defaultValue: 'https://arrzone.vercel.app',
);

Future<void> main() async {
  WidgetsFlutterBinding.ensureInitialized();
  if (Platform.isAndroid) {
    await Firebase.initializeApp();
  } else {
    await Firebase.initializeApp(options: _firebaseOptions);
  }
  runApp(const StoreOpsApp());
}

class StoreOpsApp extends StatelessWidget {
  const StoreOpsApp({super.key});

  @override
  Widget build(BuildContext context) {
    final colorScheme = ColorScheme.fromSeed(
      seedColor: const Color(0xFF2457E6),
      brightness: Brightness.light,
    );

    return MaterialApp(
      title: 'ARRZONE Store Ops',
      debugShowCheckedModeBanner: false,
      theme: ThemeData(
        colorScheme: colorScheme,
        scaffoldBackgroundColor: const Color(0xFFF6F1E7),
        useMaterial3: true,
        appBarTheme: AppBarTheme(
          backgroundColor: Colors.transparent,
          foregroundColor: colorScheme.onSurface,
          elevation: 0,
          centerTitle: false,
        ),
        cardTheme: CardThemeData(
          color: Colors.white.withValues(alpha: 0.9),
          elevation: 0,
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(28),
            side: BorderSide(color: colorScheme.outlineVariant),
          ),
        ),
        inputDecorationTheme: InputDecorationTheme(
          filled: true,
          fillColor: Colors.white,
          border: OutlineInputBorder(
            borderRadius: BorderRadius.circular(18),
            borderSide: BorderSide(color: colorScheme.outlineVariant),
          ),
          enabledBorder: OutlineInputBorder(
            borderRadius: BorderRadius.circular(18),
            borderSide: BorderSide(color: colorScheme.outlineVariant),
          ),
          focusedBorder: OutlineInputBorder(
            borderRadius: BorderRadius.circular(18),
            borderSide: BorderSide(color: colorScheme.primary, width: 1.4),
          ),
        ),
      ),
      home: const StoreOpsHomePage(),
    );
  }
}

class StoreOpsHomePage extends StatelessWidget {
  const StoreOpsHomePage({super.key});

  @override
  Widget build(BuildContext context) {
    return StreamBuilder<User?>(
      stream: FirebaseAuth.instance.authStateChanges(),
      builder: (context, snapshot) {
        final user = snapshot.data;
        return user == null
            ? const SignInScreen()
            : BootstrapScreen(user: user);
      },
    );
  }
}

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
        _errorMessage = _firebaseErrorMessage(error);
      });
    } catch (error) {
      setState(() {
        _errorMessage = 'Google sign-in failed: $error';
      });
    } finally {
      if (mounted) {
        setState(() {
          _pending = false;
        });
      }
    }
  }

  Future<void> _signIn() async {
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
        _errorMessage = _firebaseErrorMessage(error);
      });
    } catch (error) {
      setState(() {
        _errorMessage = 'Unexpected error: $error';
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
        child: SingleChildScrollView(
          padding: const EdgeInsets.fromLTRB(20, 18, 20, 28),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                'ARRZONE Store Ops',
                style: theme.textTheme.headlineMedium?.copyWith(
                  fontWeight: FontWeight.w700,
                ),
              ),
              const SizedBox(height: 10),
              Text(
                'Production sign-in for store staff and admins. The app gets its Firebase token and resolves access automatically after login.',
                style: theme.textTheme.bodyLarge?.copyWith(
                  color: const Color(0xFF5B6270),
                  height: 1.5,
                ),
              ),
              const SizedBox(height: 22),
              _SectionCard(
                title: 'Sign in',
                subtitle:
                    'Use the account already mapped in Firebase and the ARRZONE backend. Operators do not need to enter tokens or backend URLs.',
                child: Column(
                  children: [
                    SizedBox(
                      width: double.infinity,
                      child: OutlinedButton.icon(
                        onPressed: _pending ? null : _signInWithGoogle,
                        icon: const Icon(Icons.account_circle_outlined),
                        label: Text(
                          _pending ? 'Please wait...' : 'Continue with Google',
                        ),
                      ),
                    ),
                    const SizedBox(height: 18),
                    Row(
                      children: [
                        Expanded(
                          child: Divider(
                            color: Theme.of(context).colorScheme.outlineVariant,
                          ),
                        ),
                        Padding(
                          padding: const EdgeInsets.symmetric(horizontal: 12),
                          child: Text(
                            'or',
                            style: theme.textTheme.bodySmall?.copyWith(
                              color: const Color(0xFF727989),
                              fontWeight: FontWeight.w600,
                            ),
                          ),
                        ),
                        Expanded(
                          child: Divider(
                            color: Theme.of(context).colorScheme.outlineVariant,
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 18),
                    TextField(
                      controller: _emailController,
                      keyboardType: TextInputType.emailAddress,
                      autofillHints: const [AutofillHints.username],
                      textInputAction: TextInputAction.next,
                      decoration: const InputDecoration(
                        labelText: 'Email',
                        hintText: 'staff@example.com',
                      ),
                    ),
                    const SizedBox(height: 14),
                    TextField(
                      controller: _passwordController,
                      obscureText: true,
                      autofillHints: const [AutofillHints.password],
                      onSubmitted: (_) => _pending ? null : _signIn(),
                      decoration: const InputDecoration(labelText: 'Password'),
                    ),
                    const SizedBox(height: 18),
                    SizedBox(
                      width: double.infinity,
                      child: FilledButton.icon(
                        onPressed: _pending ? null : _signIn,
                        icon: _pending
                            ? const SizedBox(
                                width: 18,
                                height: 18,
                                child: CircularProgressIndicator(
                                  strokeWidth: 2,
                                ),
                              )
                            : const Icon(Icons.login_rounded),
                        label: Text(_pending ? 'Signing in...' : 'Sign in'),
                      ),
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 18),
              _StatusBanner(
                title: 'Backend',
                message: _backendBaseUrl,
                color: const Color(0xFF2457E6),
                icon: Icons.cloud_done_outlined,
              ),
              if (_errorMessage.isNotEmpty) ...[
                const SizedBox(height: 18),
                _StatusBanner(
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
    );
  }
}

class BootstrapScreen extends StatefulWidget {
  const BootstrapScreen({super.key, required this.user});

  final User user;

  @override
  State<BootstrapScreen> createState() => _BootstrapScreenState();
}

class _BootstrapScreenState extends State<BootstrapScreen> {
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
  void didUpdateWidget(covariant BootstrapScreen oldWidget) {
    super.didUpdateWidget(oldWidget);
    if (widget.user.uid != oldWidget.user.uid) {
      WidgetsBinding.instance.addPostFrameCallback((_) {
        _resolveActor(forceRefreshToken: true);
      });
    }
  }

  Future<void> _resolveActor({bool forceRefreshToken = false}) async {
    FocusScope.of(context).unfocus();
    setState(() {
      _pending = true;
      _errorMessage = '';
    });

    try {
      final idToken = await widget.user.getIdToken(forceRefreshToken);
      if (idToken == null || idToken.isEmpty) {
        throw const ApiException(
          'Firebase did not return an ID token. Sign in again.',
        );
      }

      final actor = await _service.resolveActor(
        baseUrl: _backendBaseUrl,
        idToken: idToken,
      );

      if (!mounted) {
        return;
      }

      setState(() {
        _actor = actor;
      });
    } on FirebaseAuthException catch (error) {
      setState(() {
        _actor = null;
        _errorMessage = _firebaseErrorMessage(error);
      });
    } on ApiException catch (error) {
      setState(() {
        _actor = null;
        _errorMessage = error.message;
      });
    } catch (error) {
      setState(() {
        _actor = null;
        _errorMessage = 'Unexpected error: $error';
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
    await GoogleSignIn().signOut();
    await FirebaseAuth.instance.signOut();
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);

    return Scaffold(
      body: AppBackdrop(
        child: SingleChildScrollView(
          padding: const EdgeInsets.fromLTRB(20, 18, 20, 28),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                'ARRZONE Store Ops',
                style: theme.textTheme.headlineMedium?.copyWith(
                  fontWeight: FontWeight.w700,
                ),
              ),
              const SizedBox(height: 10),
              Text(
                'Signed in as ${widget.user.email ?? widget.user.uid}. This device now reuses the Firebase session and bootstraps against the production ARRZONE backend automatically.',
                style: theme.textTheme.bodyLarge?.copyWith(
                  color: const Color(0xFF5B6270),
                  height: 1.5,
                ),
              ),
              const SizedBox(height: 22),
              _SectionCard(
                title: 'Session',
                subtitle:
                    'No runtime infrastructure prompts. The backend is fixed at build time and your session is reused on next launch.',
                child: Column(
                  children: [
                    DecoratedBox(
                      decoration: BoxDecoration(
                        color: Colors.white,
                        borderRadius: BorderRadius.circular(18),
                        border: Border.all(
                          color: Theme.of(context).colorScheme.outlineVariant,
                        ),
                      ),
                      child: Padding(
                        padding: const EdgeInsets.all(16),
                        child: Row(
                          children: [
                            Icon(
                              Icons.cloud_done_outlined,
                              color: Theme.of(context).colorScheme.primary,
                            ),
                            const SizedBox(width: 12),
                            Expanded(
                              child: Text(
                                _backendBaseUrl,
                                style: theme.textTheme.bodyMedium?.copyWith(
                                  fontWeight: FontWeight.w600,
                                ),
                              ),
                            ),
                          ],
                        ),
                      ),
                    ),
                    const SizedBox(height: 18),
                    Row(
                      children: [
                        Expanded(
                          child: OutlinedButton.icon(
                            onPressed: _pending
                                ? null
                                : () => _resolveActor(forceRefreshToken: true),
                            icon: const Icon(Icons.refresh_rounded),
                            label: const Text('Refresh access'),
                          ),
                        ),
                        const SizedBox(width: 12),
                        Expanded(
                          child: FilledButton.icon(
                            onPressed: _pending ? null : _signOut,
                            icon: const Icon(Icons.logout_rounded),
                            label: const Text('Sign out'),
                          ),
                        ),
                      ],
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 18),
              if (_pending)
                const _StatusBanner(
                  title: 'Resolving mobile actor',
                  message:
                      'Fetching a fresh Firebase token and resolving your role, tenant, and branch scope from the backend.',
                  color: Color(0xFF2457E6),
                  icon: Icons.sync_rounded,
                )
              else if (_errorMessage.isNotEmpty)
                _StatusBanner(
                  title: 'Bootstrap failed',
                  message: _errorMessage,
                  color: const Color(0xFF9F2D2D),
                  icon: Icons.error_outline_rounded,
                )
              else if (_actor != null)
                const _StatusBanner(
                  title: 'Ready',
                  message:
                      'The app is signed in and the backend resolved this device session successfully.',
                  color: Color(0xFF1B7A4B),
                  icon: Icons.verified_user_outlined,
                ),
              if (_actor != null) ...[
                const SizedBox(height: 18),
                _ActorSummaryCard(actor: _actor!),
                const SizedBox(height: 18),
                _SectionCard(
                  title: 'Counter flows next',
                  subtitle:
                      'The login and bootstrap path is now production-style. Scanner, lookup, purchase, and redemption screens can build on top of this session.',
                  child: const Wrap(
                    spacing: 12,
                    runSpacing: 12,
                    children: [
                      _ActionChip(
                        icon: Icons.qr_code_scanner_rounded,
                        title: 'Scan member QR',
                        caption:
                            'Use native camera workflows once scanner screens are added.',
                      ),
                      _ActionChip(
                        icon: Icons.call_rounded,
                        title: 'Phone search',
                        caption: 'Fallback lookup for earning and recovery.',
                      ),
                      _ActionChip(
                        icon: Icons.receipt_long_rounded,
                        title: 'Add purchase',
                        caption:
                            'Reuse the existing purchase-add API and idempotency contract.',
                      ),
                      _ActionChip(
                        icon: Icons.redeem_rounded,
                        title: 'Redeem reward',
                        caption:
                            'Keep redemption live-only with server validation.',
                      ),
                    ],
                  ),
                ),
              ],
            ],
          ),
        ),
      ),
    );
  }
}

class AppBackdrop extends StatelessWidget {
  const AppBackdrop({super.key, required this.child});

  final Widget child;

  @override
  Widget build(BuildContext context) {
    final colorScheme = Theme.of(context).colorScheme;
    return DecoratedBox(
      decoration: BoxDecoration(
        gradient: LinearGradient(
          colors: [
            const Color(0xFFF6F1E7),
            const Color(0xFFF0E7D8),
            colorScheme.primary.withValues(alpha: 0.08),
          ],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
      ),
      child: SafeArea(child: child),
    );
  }
}

class MobileActor {
  const MobileActor({
    required this.actorType,
    required this.authMode,
    required this.role,
    required this.defaultRoute,
    required this.userId,
    required this.authUserId,
    required this.availableSurfaces,
    required this.branchIds,
    this.tenantId,
    this.staffUserId,
    this.platformAdminUserId,
  });

  factory MobileActor.fromMap(Map<String, dynamic> map) {
    return MobileActor(
      actorType: map['actorType'] as String? ?? 'unknown',
      authMode: map['authMode'] as String? ?? 'unknown',
      role: map['role'] as String? ?? 'unknown',
      defaultRoute: map['defaultRoute'] as String? ?? '/',
      userId: map['userId'] as String? ?? '',
      authUserId: map['authUserId'] as String? ?? '',
      tenantId: map['tenantId'] as String?,
      staffUserId: map['staffUserId'] as String?,
      platformAdminUserId: map['platformAdminUserId'] as String?,
      availableSurfaces:
          ((map['availableSurfaces'] as List<dynamic>?) ?? const [])
              .map((value) => value.toString())
              .toList(),
      branchIds: ((map['branchIds'] as List<dynamic>?) ?? const [])
          .map((value) => value.toString())
          .toList(),
    );
  }

  final String actorType;
  final String authMode;
  final String role;
  final String defaultRoute;
  final String userId;
  final String authUserId;
  final String? tenantId;
  final String? staffUserId;
  final String? platformAdminUserId;
  final List<String> availableSurfaces;
  final List<String> branchIds;
}

class MobileBootstrapService {
  const MobileBootstrapService();

  Future<MobileActor> resolveActor({
    required String baseUrl,
    required String idToken,
  }) async {
    final normalizedBaseUrl = normalizeBaseUrl(baseUrl);
    if (normalizedBaseUrl == null) {
      throw const ApiException(
        'ARRZONE backend URL is not configured correctly.',
      );
    }

    if (idToken.trim().isEmpty) {
      throw const ApiException('Firebase did not provide a usable ID token.');
    }

    final uri = Uri.parse('$normalizedBaseUrl/api/auth/mobile/me');
    final response = await http.get(
      uri,
      headers: {
        'accept': 'application/json',
        'authorization': 'Bearer ${idToken.trim()}',
      },
    );

    final payload = _decodeJson(response.body);
    final ok = payload['ok'] == true;
    if (!ok || response.statusCode < 200 || response.statusCode >= 300) {
      final error = payload['error'];
      final message = error is Map<String, dynamic>
          ? (error['message']?.toString() ?? 'Request failed.')
          : 'Request failed.';
      throw ApiException(message);
    }

    final data = payload['data'];
    if (data is! Map<String, dynamic>) {
      throw const ApiException(
        'Backend returned an invalid mobile actor payload.',
      );
    }

    return MobileActor.fromMap(data);
  }

  Map<String, dynamic> _decodeJson(String body) {
    if (body.trim().isEmpty) {
      return const {};
    }

    final decoded = jsonDecode(body);
    if (decoded is Map<String, dynamic>) {
      return decoded;
    }

    throw const ApiException('Backend returned malformed JSON.');
  }

  static String? normalizeBaseUrl(String rawValue) {
    final value = rawValue.trim();
    if (value.isEmpty) {
      return null;
    }

    final uri = Uri.tryParse(value);
    if (uri == null || !uri.hasScheme || uri.host.isEmpty) {
      return null;
    }

    if (uri.scheme != 'http' && uri.scheme != 'https') {
      return null;
    }

    return value.endsWith('/') ? value.substring(0, value.length - 1) : value;
  }
}

class ApiException implements Exception {
  const ApiException(this.message);

  final String message;

  @override
  String toString() => message;
}

String _firebaseErrorMessage(FirebaseAuthException error) {
  switch (error.code) {
    case 'invalid-credential':
      return 'The email or password is incorrect.';
    case 'invalid-email':
      return 'Enter a valid email address.';
    case 'user-disabled':
      return 'This Firebase account is disabled.';
    case 'network-request-failed':
      return 'Network error while contacting Firebase. Check internet access.';
    case 'too-many-requests':
      return 'Too many attempts. Wait a moment and try again.';
    case 'account-exists-with-different-credential':
      return 'This email already exists with another sign-in method.';
    case 'popup-closed-by-user':
      return 'Google sign-in was cancelled.';
    default:
      return error.message ?? 'Firebase sign-in failed.';
  }
}

class _SectionCard extends StatelessWidget {
  const _SectionCard({
    required this.title,
    required this.subtitle,
    required this.child,
  });

  final String title;
  final String subtitle;
  final Widget child;

  @override
  Widget build(BuildContext context) {
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(22),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              title,
              style: Theme.of(
                context,
              ).textTheme.titleLarge?.copyWith(fontWeight: FontWeight.w700),
            ),
            const SizedBox(height: 8),
            Text(
              subtitle,
              style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                color: const Color(0xFF616977),
                height: 1.5,
              ),
            ),
            const SizedBox(height: 18),
            child,
          ],
        ),
      ),
    );
  }
}

class _StatusBanner extends StatelessWidget {
  const _StatusBanner({
    required this.title,
    required this.message,
    required this.color,
    required this.icon,
  });

  final String title;
  final String message;
  final Color color;
  final IconData icon;

  @override
  Widget build(BuildContext context) {
    return DecoratedBox(
      decoration: BoxDecoration(
        color: color.withValues(alpha: 0.08),
        borderRadius: BorderRadius.circular(24),
        border: Border.all(color: color.withValues(alpha: 0.24)),
      ),
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Row(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Icon(icon, color: color),
            const SizedBox(width: 12),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    title,
                    style: Theme.of(context).textTheme.titleMedium?.copyWith(
                      fontWeight: FontWeight.w700,
                      color: color,
                    ),
                  ),
                  const SizedBox(height: 4),
                  Text(
                    message,
                    style: Theme.of(
                      context,
                    ).textTheme.bodyMedium?.copyWith(height: 1.5),
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _ActorSummaryCard extends StatelessWidget {
  const _ActorSummaryCard({required this.actor});

  final MobileActor actor;

  @override
  Widget build(BuildContext context) {
    return _SectionCard(
      title: 'Resolved actor',
      subtitle: actor.actorType == 'platform_admin'
          ? 'This account resolved to a platform-admin record.'
          : 'This account resolved to a store-operations record that can drive cashier and manager workflows.',
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Wrap(
            spacing: 10,
            runSpacing: 10,
            children: [
              _InfoPill(label: 'Actor type', value: actor.actorType),
              _InfoPill(label: 'Role', value: actor.role),
              _InfoPill(label: 'Auth mode', value: actor.authMode),
              _InfoPill(label: 'Default route', value: actor.defaultRoute),
            ],
          ),
          const SizedBox(height: 18),
          _InfoRow(label: 'User ID', value: actor.userId),
          _InfoRow(label: 'Auth user ID', value: actor.authUserId),
          if (actor.tenantId != null)
            _InfoRow(label: 'Tenant', value: actor.tenantId!),
          if (actor.staffUserId != null)
            _InfoRow(label: 'Staff user', value: actor.staffUserId!),
          if (actor.platformAdminUserId != null)
            _InfoRow(
              label: 'Platform admin',
              value: actor.platformAdminUserId!,
            ),
          _InfoRow(
            label: 'Available surfaces',
            value: actor.availableSurfaces.join(', '),
          ),
          _InfoRow(
            label: 'Branch scope',
            value: actor.branchIds.isEmpty
                ? 'No branch IDs returned'
                : actor.branchIds.join(', '),
          ),
        ],
      ),
    );
  }
}

class _InfoPill extends StatelessWidget {
  const _InfoPill({required this.label, required this.value});

  final String label;
  final String value;

  @override
  Widget build(BuildContext context) {
    return DecoratedBox(
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(18),
        border: Border.all(color: Theme.of(context).colorScheme.outlineVariant),
      ),
      child: Padding(
        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          mainAxisSize: MainAxisSize.min,
          children: [
            Text(
              label.toUpperCase(),
              style: Theme.of(context).textTheme.labelSmall?.copyWith(
                letterSpacing: 1.1,
                color: const Color(0xFF727989),
              ),
            ),
            const SizedBox(height: 4),
            Text(
              value,
              style: Theme.of(
                context,
              ).textTheme.bodyMedium?.copyWith(fontWeight: FontWeight.w700),
            ),
          ],
        ),
      ),
    );
  }
}

class _InfoRow extends StatelessWidget {
  const _InfoRow({required this.label, required this.value});

  final String label;
  final String value;

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 12),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            label.toUpperCase(),
            style: Theme.of(context).textTheme.labelSmall?.copyWith(
              letterSpacing: 1.1,
              color: const Color(0xFF727989),
            ),
          ),
          const SizedBox(height: 4),
          SelectableText(
            value,
            style: Theme.of(
              context,
            ).textTheme.bodyMedium?.copyWith(height: 1.5),
          ),
        ],
      ),
    );
  }
}

class _ActionChip extends StatelessWidget {
  const _ActionChip({
    required this.icon,
    required this.title,
    required this.caption,
  });

  final IconData icon;
  final String title;
  final String caption;

  @override
  Widget build(BuildContext context) {
    return SizedBox(
      width: 240,
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
              Icon(icon, color: Theme.of(context).colorScheme.primary),
              const SizedBox(height: 12),
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
