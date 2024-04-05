import 'package:evf/models/feed_item.dart';
import 'package:evf/models/feed_list.dart';

FeedList feedItems() {
  final retval = FeedList();
  retval.add(FeedItem(
    id: 'id1',
    type: FeedType.notification,
    title: 'Notification',
    content: 'You are notified of something that matters to you',
    published: DateTime(2024, 2, 1, 12, 34, 56),
    mutated: DateTime(2024, 2, 1, 12, 34, 56),
  ));
  retval.add(FeedItem(
    id: 'id2',
    type: FeedType.news,
    title: 'News item',
    content:
        'Breaking news with \ta lot of content and linebreaks\r\nThat we can even check\r\nto see if\r\nthis works in  a richt\r\ntext setting',
    published: DateTime(2024, 1, 31, 12, 34, 56),
    mutated: DateTime(2024, 2, 1, 12, 04, 56),
  ));
  retval.add(FeedItem(
    id: 'id3',
    type: FeedType.ranking,
    title: 'Ranking notification',
    content: 'You\'re position dropped a little bitfurther than a',
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
  return retval;
}
