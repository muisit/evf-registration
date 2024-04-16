import 'package:evf/environment.dart';

import 'interface.dart';

Future<bool> addFollowing(String id) async {
  Environment.debug("calling addFollowing for $id");
  final api = Interface.create(path: '/device/follow', data: {
    'follow': {'fencer': id}
  });
  try {
    var content = await api.post();
    if (content.containsKey('status') && content['status'] == 'ok') {
      return true;
    }
    Environment.debug("addFollowing returned content is $content");
  } on NetworkError {
    Environment.debug("addFollowing fails due to exception");
  }
  Environment.debug("end of addFollowing, error situation");
  return false;
}
