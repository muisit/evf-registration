import 'package:evf/environment.dart';
import 'package:flutter/material.dart';

class AppLogo extends StatelessWidget {
  const AppLogo({super.key});

  @override
  Widget build(BuildContext context) {
    return Text('EVF ${Environment.instance.authToken}');
  }
}
