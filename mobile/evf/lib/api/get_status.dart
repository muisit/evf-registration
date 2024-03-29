// Load the initial status from the back-end
// If the current device id is not recognised, this will cause an unAuth error (403)
// which indicates we need to reregister. Apparently the device was removed from
// the backend.

import 'package:evf/models/status.dart';
//import 'package:evf/models/device.dart';
import 'package:evf/environment.dart';
import 'interface.dart';

Future<Status> getStatus({int tries = 0}) async {
  Environment.debug("calling getStatus");
  final api = Interface.create(path: '/device/status');
  var content = await api.get();
  return Status.fromJson(content);
}
