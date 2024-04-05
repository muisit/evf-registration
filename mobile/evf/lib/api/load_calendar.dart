// Load all available calendar items from the back-end.
// At this point, we assume that this list is not very huge, so we can just load everything at
// once. If calendar lists become too large at some point, we may need to apply some pagination
// to load it in parts

import 'package:evf/env/calendar_items.dart';
import 'package:evf/models/calendar.dart';
//import 'package:evf/environment.dart';
//import 'interface.dart';

Future<List<Calendar>> loadCalendar({DateTime? lastDate}) async {
  return Future.value(calendarItems());
  /*
  Environment.instance.debug("calling loadCalendar");
  final api = Interface.create(path: '/device/calendar');
  if (lastDate != null) {
    api.data['last'] = lastDate.toIso8601String();
  }
  var content = await api.get();
  var lst = content as List<dynamic>;
  foreach(final item in lst) {
    retval.add(Calendar.fromJson(item as Map<String, dynamic>));
  }
  return retval;
  */
}
