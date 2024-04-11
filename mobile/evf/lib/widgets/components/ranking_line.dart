import 'package:evf/models/ranking_position.dart';
import 'package:evf/styles.dart';
import 'package:flutter/material.dart';

class RankingLine extends StatelessWidget {
  final RankingPosition item;
  const RankingLine({super.key, required this.item});

  @override
  Widget build(BuildContext context) {
    return Expanded(
        child: Ink(
            color: ((item.position % 2) == 0) ? AppStyles.stripes : Colors.white,
            child: Row(
              crossAxisAlignment: CrossAxisAlignment.start,
              mainAxisAlignment: MainAxisAlignment.start,
              children: [
                Text("${item.position.toString()}."),
                const Icon(Icons.favorite_outline),
                Text(item.lastName),
                Text(item.firstName),
                Text(item.country),
                Text(item.points.toStringAsFixed(2))
              ],
            )));
  }
}
