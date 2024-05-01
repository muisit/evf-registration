// The FeedMessage class holds standard data for a FeedMessage that is translatable.
// The same translations are used for the Firebase notifications

import 'package:evf/environment.dart';

class FeedMessage {
  String label; // the label to localise
  String owner; // determine if this is a 'they ..' or a 'we...' message
  String name; // name of the owner, firstname-lastname
  String category; // ... ended up on place <place> in <weapon> <category>
  String weapon; // .. current ranking is place <place> in <weapon> <category>
  String place;
  String location;
  DateTime? datetime; // ... is registered for <weapon> <category> in <location> on <date>

  FeedMessage.fromJson(Map<String, dynamic> doc)
      : label = doc['label'] ?? '',
        owner = doc['owner'] ?? '',
        name = doc['name'] ?? '',
        category = doc['category'] ?? '',
        weapon = doc['weapon'] ?? '',
        place = doc['place'] ?? '',
        location = doc['location'] ?? '',
        datetime = DateTime.tryParse(doc['date']);

  @override
  String toString() {
    String retval = '';
    final date = Environment.instance.localizations!.calendarDate(datetime ?? DateTime(2000, 1, 1));
    switch (label) {
      case "notifyYouResult":
        retval = Environment.instance.localizations!.notifyYouResult(category, location, place, weapon);
      case "notifyTheyResult":
        retval = Environment.instance.localizations!.notifyTheyResult(category, location, name, place, weapon);
      case "notifyYouRanking":
        retval = Environment.instance.localizations!.notifyYouRanking(category, place, weapon);
      case "notifyTheyRanking":
        retval = Environment.instance.localizations!.notifyTheyRanking(category, name, place, weapon);
      case "notifyYouRegister":
        retval = Environment.instance.localizations!.notifyYouRegister(category, date, location, weapon);
      case "notifyTheyRegister":
        retval = Environment.instance.localizations!.notifyTheyRegister(category, date, location, name, weapon);
      case "notifyYouHandout":
        retval = Environment.instance.localizations!.notifyYouHandout(location);
      case "notifyTheyHandout":
        retval = Environment.instance.localizations!.notifyTheyHandout(location, name);
      case "notifyYouCheckin":
        retval = Environment.instance.localizations!.notifyYouCheckin(location);
      case "notifyTheyChecking":
        retval = Environment.instance.localizations!.notifyTheyCheckin(location, name);
      case "notifyYouCheckout":
        retval = Environment.instance.localizations!.notifyYouCheckout;
      case "notifyTheyCheckout":
        retval = Environment.instance.localizations!.notifyTheyCheckout(name);
      case "notifyYouCheckoutError":
        retval = Environment.instance.localizations!.notifyYouCheckoutError;
      case "notifyTheyCheckoutError":
        retval = Environment.instance.localizations!.notifyTheyCheckoutError(name);
      case "notifyYouCheckedOut":
        retval = Environment.instance.localizations!.notifyYouCheckedOut(location);
      case "notifyTheyCheckedOut":
        retval = Environment.instance.localizations!.notifyTheyCheckedOut(location, name);
    }
    return retval;
  }
}
