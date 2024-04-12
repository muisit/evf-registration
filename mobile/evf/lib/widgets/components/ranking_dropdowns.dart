import 'package:evf/environment.dart';
import 'package:evf/styles.dart';
import 'package:evf/widgets/basic/dropdown.dart';
import 'package:flutter/material.dart';
import 'package:flutter_gen/gen_l10n/app_localizations.dart';

typedef RankingSelectionCallBack = void Function(String, String);

class RankingDropdowns extends StatelessWidget {
  final RankingSelectionCallBack callback;
  final String category;
  final String weapon;
  const RankingDropdowns({super.key, required this.callback, required this.category, required this.weapon});

  @override
  Widget build(BuildContext context) {
    final categoryEntries = [
      DropdownOption('Cat 1', AppLocalizations.of(context)!.labelCategoryCat1),
      DropdownOption('Cat 2', AppLocalizations.of(context)!.labelCategoryCat2),
      DropdownOption('Cat 3', AppLocalizations.of(context)!.labelCategoryCat3),
      DropdownOption('Cat 4', AppLocalizations.of(context)!.labelCategoryCat4),
    ];

    final weaponEntries = [
      DropdownOption('Mens Foil', AppLocalizations.of(context)!.labelWeaponMF),
      DropdownOption('Mens Epee', AppLocalizations.of(context)!.labelWeaponME),
      DropdownOption('Mens Sabre', AppLocalizations.of(context)!.labelWeaponMS),
      DropdownOption('Womens Foil', AppLocalizations.of(context)!.labelWeaponWF),
      DropdownOption('Womens Epee', AppLocalizations.of(context)!.labelWeaponWE),
      DropdownOption('Womens Sabre', AppLocalizations.of(context)!.labelWeaponWS),
    ];

    /*final catLabel = AppLocalizations.of(context)!.labelCategoryLongest;
    final weaponLabel = AppLocalizations.of(context)!.labelWeaponLongest;
    final catSpan = TextSpan(
      text: catLabel,
      style: AppStyles.plainText,
    );
    final catTp = TextPainter(text: catSpan, textDirection: TextDirection.ltr);
    catTp.layout();
    final catWidth = catTp.width;

    final wpnSpan = TextSpan(
      text: weaponLabel,
      style: AppStyles.plainText,
    );
    final wpnTp = TextPainter(text: wpnSpan, textDirection: TextDirection.ltr);
    wpnTp.layout();
    final wpnWidth = wpnTp.width;*/

    Environment.debug("building widget using $weapon and $category");
    return Padding(
        padding: const EdgeInsets.fromLTRB(0, 12, 0, 0),
        child: Row(
          crossAxisAlignment: CrossAxisAlignment.start,
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Dropdown(
                options: weaponEntries,
                value: weapon,
                callback: (DropdownOption wpn) {
                  callback(category, wpn.value);
                }),
            const SizedBox(width: 8),
            Dropdown(
                options: categoryEntries,
                value: category,
                callback: (DropdownOption cat) {
                  callback(cat.value, weapon);
                }),
          ],
        ));
  }
}
