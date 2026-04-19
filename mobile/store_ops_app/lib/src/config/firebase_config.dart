import 'package:firebase_core/firebase_core.dart';

const firebaseOptions = FirebaseOptions(
  apiKey: 'AIzaSyAnxV0PGi389_WcyvFTB_5_JM8I_z0oGYI',
  appId: '1:519662663037:web:556d088d0f0b14c507c814',
  messagingSenderId: '519662663037',
  projectId: 'arrcloud-637ec',
  authDomain: 'arrcloud-637ec.firebaseapp.com',
);

const backendBaseUrl = String.fromEnvironment(
  'ARRZONE_API_BASE_URL',
  defaultValue: 'https://arrzone.vercel.app',
);
