import 'package:flutter_test/flutter_test.dart';
import 'package:store_ops_app/main.dart';

void main() {
  test('normalizes backend base URLs', () {
    expect(
      MobileBootstrapService.normalizeBaseUrl('http://10.0.2.2:3000/'),
      'http://10.0.2.2:3000',
    );
    expect(
      MobileBootstrapService.normalizeBaseUrl('https://arrzone.vercel.app'),
      'https://arrzone.vercel.app',
    );
    expect(MobileBootstrapService.normalizeBaseUrl('localhost:3000'), isNull);
  });
}
