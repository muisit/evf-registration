import 'package:evf/environment.dart';

import 'interface.dart';

Future<bool> removeFollowing(String id) async {
  Environment.debug("calling removeFollowing for $id");
  final api = Interface.create(path: '/device/follow', data: {
    'follow': {
      'fencer': id,
      'preferences': ['unfollow']
    }
  });
  try {
    var content = await api.post();
    if (content.containsKey('status') && content['status'] == 'ok') {
      return true;
    }
    Environment.debug("removeFollowing returned content is $content");
  } on NetworkError {
    Environment.error("removeFollowing fails");
  }
  Environment.error("end of removeFollowing, error situation");
  return false;
}