import 'package:evf/environment.dart';
import 'package:evf/widgets/components/ranking_dropdowns.dart';
import 'package:evf/widgets/components/ranking_table.dart';
import 'package:flutter/material.dart';

class RankPage extends StatefulWidget {
  const RankPage({super.key});

  @override
  State<RankPage> createState() => _RankPageState();
}

class _RankPageState extends State<RankPage> {
  String currentCategory = 'Cat 1';
  String currentWeapon = 'Mens Foil';

  @override
  initState() {
    super.initState();
    Environment.instance.rankingProvider.loadItems();
    Environment.instance.preference('category').then((value) {
      if (value == '') {
        value = 'Cat 1';
      }
      Environment.debug("setting currentCategory based on preference $value");
      setState(() {
        currentCategory = value;
      });
    });
    Environment.instance.preference('weapon').then((value) {
      if (value == '') {
        value = 'Mens Foil';
      }
      Environment.debug("setting currentWeapon based on preference $value");
      setState(() {
        currentWeapon = value;
      });
    });
  }

  @override
  Widget build(BuildContext context) {
    return NotificationListener<OverscrollNotification>(
        onNotification: (OverscrollNotification notification) {
          Environment.debug("overscroll detected ${notification.overscroll}");
          return false;
        },
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.center,
          children: [
            RankingDropdowns(callback: _setCategoryAndWeapon, category: currentCategory, weapon: currentWeapon),
            Expanded(child: RankingTable(category: currentCategory, weapon: currentWeapon))
          ],
        ));
  }

  void _setCategoryAndWeapon(String category, String weapon) {
    setState(() {
      Environment.debug("setting category to $category and weapon to $weapon");
      currentCategory = category;
      currentWeapon = weapon;
    });
  }
}
