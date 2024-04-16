import 'package:evf/environment.dart';
import 'package:evf/models/ranking.dart';
import 'package:flutter/material.dart';

import 'ranking_table.dart';
import 'ranking_title.dart';

class RankingDisplay extends StatelessWidget {
  final String category;
  final String weapon;
  const RankingDisplay({super.key, required this.category, required this.weapon});

  @override
  Widget build(BuildContext context) {
    Environment.debug("getting ranking for $category and $weapon");
    final Ranking ranking = Environment.instance.rankingProvider.getRankingFor(category, weapon);
    Environment.debug("ranking has ${ranking.positions.length} entries");

    final controller = ScrollController();
    return ListenableBuilder(
        listenable: Environment.instance.rankingProvider,
        builder: (BuildContext context, Widget? child) {
          return Padding(
              padding: const EdgeInsets.fromLTRB(10, 0, 10, 5),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.center,
                mainAxisAlignment: MainAxisAlignment.start,
                mainAxisSize: MainAxisSize.min,
                children: [
                  RankingTitle(ranking: ranking),
                  Expanded(
                    child: Scrollbar(
                      controller: controller,
                      child: SingleChildScrollView(
                          controller: controller,
                          child: RankingTable(
                            ranking: ranking,
                            onFavoriteTap: _performFavoriteAction,
                            onZoomTap: _performZoomAction,
                          )),
                    ),
                  )
                ],
              ));
        });
  }

  void _performFavoriteAction(String uuid) async {
    Environment.debug("clicked on favorite $uuid");
    await Environment.instance.followerProvider.toggleFollowing(uuid);
  }

  void _performZoomAction(String uuid) {
    Environment.debug("zooming into $uuid");
  }
}
