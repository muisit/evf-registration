// Load all available feed items from the back-end.
// At this point, we assume that this list is not very huge, so we can just load everything at
// once. If feed lists become too large at some point, we may need to apply some pagination
// to load it in parts

import 'package:evf/models/feed_item.dart';
import 'package:evf/models/feed_list.dart';
//import 'package:evf/environment.dart';
//import 'interface.dart';

Future<FeedList> loadFeed({DateTime? lastDate}) async {
  final retval = FeedList();
  retval.add(FeedItem(
    id: 'id1',
    type: FeedType.notification,
    title: 'Notification',
    content: 'You are notified',
    published: DateTime(2024, 2, 1, 12, 34, 56),
    mutated: DateTime(2024, 2, 1, 12, 34, 56),
  ));
  retval.add(FeedItem(
    id: 'id2',
    type: FeedType.news,
    title: 'News item',
    content: 'Breaking news',
    published: DateTime(2024, 1, 31, 12, 34, 56),
    mutated: DateTime(2024, 2, 1, 12, 04, 56),
  ));
  retval.add(FeedItem(
    id: 'id3',
    type: FeedType.ranking,
    title: 'Ranking notification',
    content: 'You\'re position dropped',
    published: DateTime(2024, 1, 28, 12, 34, 56),
    mutated: DateTime(2024, 1, 28, 12, 34, 56),
  ));
  retval.add(FeedItem(
    id: 'id4',
    type: FeedType.result,
    title: 'Result type',
    content: 'You ended up 2nd',
    published: DateTime(2024, 1, 21, 12, 34, 56),
    mutated: DateTime(2024, 1, 21, 12, 34, 56),
  ));
  retval.add(FeedItem(
    id: 'id5',
    type: FeedType.message,
    title: 'A message for you',
    content: 'I dunno whats in it',
    published: DateTime(2024, 1, 11, 12, 34, 56),
    mutated: DateTime(2024, 1, 11, 12, 34, 56),
  ));
  retval.add(FeedItem(
    id: 'id6',
    type: FeedType.friends,
    title: 'Friends did something',
    content: 'Message correlated to a friends event',
    published: DateTime(2024, 1, 1, 12, 34, 56),
    mutated: DateTime(2024, 1, 1, 12, 34, 56),
  ));
  return Future.value(retval);
  /*
  Environment.instance.debug("calling loadFeed");
  final api = Interface.create(path: '/device/feed');
  if (lastDate != null) {
    api.data['last'] = lastDate.toIso8601String();
  }
  var content = await api.get();
  return FeedList.fromJson(content['list'] as List<dynamic>);
  */
}
