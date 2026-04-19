import 'package:flutter/material.dart';
import 'package:store_ops_app/src/app/store_ops_auth_gate.dart';
import 'package:store_ops_app/src/app/store_ops_theme.dart';

class StoreOpsApp extends StatelessWidget {
  const StoreOpsApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'ARRZONE Store Ops',
      debugShowCheckedModeBanner: false,
      theme: buildStoreOpsTheme(),
      home: const StoreOpsAuthGate(),
    );
  }
}
