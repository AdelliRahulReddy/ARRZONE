import 'dart:convert';

import 'package:http/http.dart' as http;
import 'package:store_ops_app/src/config/firebase_config.dart';
import 'package:store_ops_app/src/services/mobile_bootstrap_service.dart';

class MemberLookupResult {
  const MemberLookupResult({
    required this.membershipId,
    required this.branchId,
    required this.branchName,
    required this.customerName,
    required this.maskedPhone,
    required this.planName,
    required this.status,
    required this.purchaseCount,
    required this.rewardBalance,
    this.passUrl,
    this.passToken,
    this.branchCode,
  });

  factory MemberLookupResult.fromMap(Map<String, dynamic> map) {
    final summary = map['summary'] as Map<String, dynamic>? ?? const {};
    return MemberLookupResult(
      membershipId: map['membershipId']?.toString() ?? '',
      branchId: map['branchId']?.toString() ?? '',
      branchName:
          map['branchName']?.toString() ?? map['branchId']?.toString() ?? '',
      customerName: map['customerName']?.toString() ?? 'Member',
      maskedPhone: map['maskedPhone']?.toString() ?? '',
      planName: map['planName']?.toString() ?? 'Plan',
      status: map['status']?.toString() ?? 'ACTIVE',
      purchaseCount: (summary['purchaseCount'] as num?)?.toInt() ?? 0,
      rewardBalance: (summary['rewardBalance'] as num?)?.toInt() ?? 0,
      passUrl: map['passUrl']?.toString(),
      passToken: map['passToken']?.toString(),
      branchCode: map['branchCode']?.toString(),
    );
  }

  final String membershipId;
  final String branchId;
  final String branchName;
  final String customerName;
  final String maskedPhone;
  final String planName;
  final String status;
  final int purchaseCount;
  final int rewardBalance;
  final String? passUrl;
  final String? passToken;
  final String? branchCode;
}

class StaffLookupService {
  const StaffLookupService();

  Future<MemberLookupResult> lookupByQr({
    required String idToken,
    required String qrPayload,
  }) async {
    final normalizedBaseUrl = MobileBootstrapService.normalizeBaseUrl(
      backendBaseUrl,
    );
    if (normalizedBaseUrl == null) {
      throw const ApiException(
        'ARRZONE backend URL is not configured correctly.',
      );
    }

    final uri = Uri.parse('$normalizedBaseUrl/api/v1/memberships/lookup-by-qr');
    final response = await http.post(
      uri,
      headers: {
        'accept': 'application/json',
        'content-type': 'application/json',
        'authorization': 'Bearer ${idToken.trim()}',
      },
      body: jsonEncode({'qrPayload': qrPayload}),
    );

    final payload = _decodeJson(response.body);
    final ok = payload['ok'] == true;
    if (!ok || response.statusCode < 200 || response.statusCode >= 300) {
      final error = payload['error'];
      final message = error is Map<String, dynamic>
          ? (error['message']?.toString() ?? 'Lookup failed.')
          : 'Lookup failed.';
      throw ApiException(message);
    }

    final data = payload['data'];
    if (data is! Map<String, dynamic>) {
      throw const ApiException('Lookup returned an invalid member payload.');
    }

    return MemberLookupResult.fromMap(data);
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
}
