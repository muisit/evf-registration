import 'package:evf/environment.dart';
import 'package:evf/models/fencer.dart';

class Follower {
  Fencer fencer;
  String user = '';
  bool handout = true;
  bool checkin = true;
  bool checkout = true;
  bool ranking = true;
  bool result = true;
  bool register = true;
  bool blocked = false;
  bool unfollow = false; // local setting
  bool synced = false; // local setting

  Follower(String uuid) : fencer = Fencer(uuid);
  Follower.device(String uuid)
      : fencer = Fencer(''),
        user = uuid;

  Follower.fromJson(Map<String, dynamic> doc)
      : fencer = Fencer.fromJson(doc['fencer'] ?? {}),
        user = doc['user'] ?? '' {
    Environment.debug("converting list of preferences for Follower");
    final preferences = doc['preferences'] as List<String>;
    handout = preferences.contains('handout');
    checkin = preferences.contains('checkin');
    checkout = preferences.contains('checkout');
    ranking = preferences.contains('ranking');
    result = preferences.contains('result');
    register = preferences.contains('register');
    blocked = preferences.contains('blocked');
  }

  Map<String, dynamic> toJson() {
    List<String> preferences = [];
    if (handout) preferences.add('handout');
    if (checkin) preferences.add('checkin');
    if (checkout) preferences.add('checkout');
    if (ranking) preferences.add('ranking');
    if (result) preferences.add('result');
    if (register) preferences.add('register');
    if (blocked) preferences.add('blocked');
    if (synced) preferences.add('synced');

    return {
      'fencer': fencer,
      'user': user,
      'preferences': preferences,
    };
  }
}
