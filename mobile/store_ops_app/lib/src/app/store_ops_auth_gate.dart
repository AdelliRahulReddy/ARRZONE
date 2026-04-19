import 'package:firebase_auth/firebase_auth.dart';
import 'package:flutter/material.dart';
import 'package:store_ops_app/src/auth/sign_in_screen.dart';
import 'package:store_ops_app/src/workspace/workspace_gate.dart';

class StoreOpsAuthGate extends StatelessWidget {
  const StoreOpsAuthGate({super.key});

  @override
  Widget build(BuildContext context) {
    return StreamBuilder<User?>(
      stream: FirebaseAuth.instance.authStateChanges(),
      builder: (context, snapshot) {
        final user = snapshot.data;
        if (user == null) {
          return const SignInScreen();
        }

        return WorkspaceGate(user: user);
      },
    );
  }
}
