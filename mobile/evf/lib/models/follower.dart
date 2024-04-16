class Follower {
  String fencer;
  bool handout = true;
  bool checkin = true;
  bool checkout = true;
  bool ranking = true;
  bool result = true;
  bool register = true;
  bool blocked = false;
  bool unfollow = false; // local setting
  bool synced = false; // local setting

  Follower(this.fencer);

  Follower.fromJson(Map<String, dynamic> doc) : fencer = doc['fencer'] as String {
    final preferences = doc['preferences'] as Map<String, dynamic>;
    handout = preferences.containsKey('handout');
    checkin = preferences.containsKey('checkin');
    checkout = preferences.containsKey('checkout');
    ranking = preferences.containsKey('ranking');
    result = preferences.containsKey('result');
    register = preferences.containsKey('register');
    blocked = preferences.containsKey('blocked');
  }

  Map<String, dynamic> toJson() {
    Map<String, bool> preferences = {};
    if (handout) preferences['handout'] = true;
    if (checkin) preferences['checkin'] = true;
    if (checkout) preferences['checkout'] = true;
    if (ranking) preferences['ranking'] = true;
    if (result) preferences['result'] = true;
    if (register) preferences['register'] = true;
    if (blocked) preferences['blocked'] = true;
    if (synced) preferences['synced'] = true;

    return {'fencer': fencer, 'preferences': preferences};
  }
}
