import 'package:evf/models/account_data.dart';
import 'package:flutter/material.dart';

class AccountSettings extends StatefulWidget {
  final AccountData data;
  const AccountSettings({super.key, required this.data});
  @override
  State<AccountSettings> createState() => _AccountSettingsState();
}

class _AccountSettingsState extends State<AccountSettings> {
  @override
  initState() {
    super.initState();
  }

  @override
  Widget build(BuildContext context) {
    return Text('1');
  }
}
