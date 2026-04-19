import 'dart:convert';

import 'package:http/http.dart' as http;
import 'package:store_ops_app/src/config/firebase_config.dart';

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

extension MobileActorPresentation on MobileActor {
  bool get isPlatformAdmin => actorType == 'platform_admin';

  bool get isBusinessAdmin =>
      role == 'BUSINESS_ADMIN' || role == 'MERCHANT_ADMIN';

  bool get isManager => role == 'MANAGER';

  String get roleLabel {
    switch (role) {
      case 'CASHIER':
        return 'Counter Staff';
      case 'MANAGER':
        return 'Store Manager';
      case 'BUSINESS_ADMIN':
      case 'MERCHANT_ADMIN':
        return 'Business Admin';
      case 'PLATFORM_ADMIN':
        return 'Platform Admin';
      default:
        return role.replaceAll('_', ' ').toLowerCase();
    }
  }

  String get workspaceTitle {
    if (isPlatformAdmin) {
      return 'Platform Control';
    }

    if (branchIds.isEmpty) {
      return 'Store Workspace';
    }

    if (branchIds.length == 1) {
      return prettifyBranchId(branchIds.first);
    }

    return '${prettifyBranchId(branchIds.first)} +${branchIds.length - 1}';
  }

  String get workspaceSubtitle {
    if (isPlatformAdmin) {
      return 'Cross-tenant access is active for platform oversight.';
    }

    if (isBusinessAdmin) {
      return 'Store operations, staffing, plans, and branch controls are available.';
    }

    if (isManager) {
      return 'Counter operations and manager approvals are ready for this shift.';
    }

    return 'Counter tools are ready. Scan, search, add purchase, and redeem rewards.';
  }

  String get branchSummary {
    if (branchIds.isEmpty) {
      return 'No assigned store';
    }

    if (branchIds.length == 1) {
      return prettifyBranchId(branchIds.first);
    }

    return '${branchIds.length} assigned stores';
  }

  List<String> get displayBranches => branchIds.map(prettifyBranchId).toList();

  static String prettifyBranchId(String branchId) {
    final value = branchId.split(':').last;
    return value
        .split(RegExp(r'[-_]'))
        .where((part) => part.isNotEmpty)
        .map((part) => '${part[0].toUpperCase()}${part.substring(1)}')
        .join(' ');
  }
}

class MobileBootstrapService {
  const MobileBootstrapService();

  Future<MobileActor> resolveActor({required String idToken}) async {
    final normalizedBaseUrl = normalizeBaseUrl(backendBaseUrl);
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
