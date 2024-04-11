import 'package:evf/environment.dart';
import 'package:evf/models/ranking.dart';
import 'package:flutter/material.dart';

import 'ranking_title.dart';
import 'ranking_line.dart';

class RankingTable extends StatelessWidget {
  final String category;
  final String weapon;
  const RankingTable({super.key, required this.category, required this.weapon});

  @override
  Widget build(BuildContext context) {
    Environment.debug("getting ranking for $category and $weapon");
    final Ranking ranking = Environment.instance.rankingProvider.getRankingFor(category, weapon);

    return ListenableBuilder(
        listenable: Environment.instance.rankingProvider,
        builder: (BuildContext context, Widget? child) {
          return Padding(
              padding: const EdgeInsets.fromLTRB(0, 12, 0, 0),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                mainAxisAlignment: MainAxisAlignment.start,
                children: [
                  RankingTitle(ranking: ranking),
                  //Expanded(
                  //    child: ListView.builder(
                  //        itemBuilder: (BuildContext context, int index) => RankingLine(item: ranking.positions[index]),
                  //        itemCount: ranking.positions.length))
                ],
              ));
        });
  }
}
