import 'package:evf/models/feed_item.dart';
import 'package:flutter/material.dart';
import 'feed_logo.dart';

class FeedComponent extends StatelessWidget {
  final FeedItem item;
  const FeedComponent({super.key, required this.item});

  @override
  Widget build(BuildContext context) {
    return Row(children: [
      FeedLogo(type: item.type),
      Expanded(
          child: Column(
        children: [Text(item.title), Text(item.content)],
      ))
    ]);
  }
}
