import 'package:evf/environment.dart';

class AccountPreferences {
  List<String> follower = [];
  List<String> following = [];

  AccountPreferences();

  AccountPreferences.fromJson(Map<String, dynamic> doc) {
    Environment.debug("Converting account preference sublists");

    if (doc['follower'] != null) {
      follower = (doc['follower'] as List<dynamic>).map<String>((d) => d.toString()).toList();
    }

    if (doc['following'] != null) {
      Environment.debug("converting following list");
      following = (doc['following'] as List<dynamic>).map<String>((d) => d.toString()).toList();
    }
    Environment.debug("end of pref conversion");
  }

  Map<String, dynamic> toJson() => {
        'follower': follower,
        'following': following,
      };
}
