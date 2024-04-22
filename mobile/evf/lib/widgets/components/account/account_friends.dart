import 'package:evf/models/account_data.dart';
import 'package:flutter/material.dart';

class AccountFriends extends StatefulWidget {
  final AccountData data;
  const AccountFriends({super.key, required this.data});
  @override
  State<AccountFriends> createState() => _AccountFriendsState();
}

class _AccountFriendsState extends State<AccountFriends> {
  @override
  initState() {
    super.initState();
  }

  @override
  Widget build(BuildContext context) {
    return Text('1');
  }
}
