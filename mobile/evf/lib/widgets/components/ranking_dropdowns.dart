import 'package:evf/environment.dart';
import 'package:evf/styles.dart';
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
      DropdownMenuEntry<String>(value: 'Cat 1', label: AppLocalizations.of(context)!.labelCategoryCat1),
      DropdownMenuEntry<String>(value: 'Cat 2', label: AppLocalizations.of(context)!.labelCategoryCat2),
      DropdownMenuEntry<String>(value: 'Cat 3', label: AppLocalizations.of(context)!.labelCategoryCat3),
      DropdownMenuEntry<String>(value: 'Cat 4', label: AppLocalizations.of(context)!.labelCategoryCat4),
    ];

    final weaponEntries = [
      DropdownMenuEntry<String>(value: 'Mens Foil', label: AppLocalizations.of(context)!.labelWeaponMF),
      DropdownMenuEntry<String>(value: 'Mens Epee', label: AppLocalizations.of(context)!.labelWeaponME),
      DropdownMenuEntry<String>(value: 'Mens Sabre', label: AppLocalizations.of(context)!.labelWeaponMS),
      DropdownMenuEntry<String>(value: 'Womens Foil', label: AppLocalizations.of(context)!.labelWeaponWF),
      DropdownMenuEntry<String>(value: 'Womens Epee', label: AppLocalizations.of(context)!.labelWeaponWE),
      DropdownMenuEntry<String>(value: 'Womens Sabre', label: AppLocalizations.of(context)!.labelWeaponWS),
    ];

    final catLabel = AppLocalizations.of(context)!.labelCategoryLongest;
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
    final wpnWidth = wpnTp.width;

    Environment.debug("building widget using $weapon and $category");
    return Padding(
        padding: const EdgeInsets.fromLTRB(0, 12, 0, 0),
        child: Row(
          crossAxisAlignment: CrossAxisAlignment.start,
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            DropdownMenu<String>(
              width: wpnWidth + 80,
              enableSearch: false,
              enableFilter: false,
              textStyle: AppStyles.plainText,
              initialSelection: weapon,
              controller: TextEditingController(),
              requestFocusOnTap: false,
              label: Text.rich(TextSpan(text: AppLocalizations.of(context)!.labelWeapon, style: AppStyles.plainText)),
              onSelected: (String? wpn) {
                callback(category, wpn ?? '');
              },
              dropdownMenuEntries: weaponEntries,
            ),
            const SizedBox(width: 8),
            DropdownMenu<String>(
              width: catWidth + 80,
              enableSearch: false,
              enableFilter: false,
              textStyle: AppStyles.plainText,
              initialSelection: category,
              controller: TextEditingController(),
              requestFocusOnTap: false,
              label: Text.rich(TextSpan(text: AppLocalizations.of(context)!.labelCategory, style: AppStyles.plainText)),
              onSelected: (String? cat) {
                callback(cat ?? '', weapon);
              },
              dropdownMenuEntries: categoryEntries,
            ),
          ],
        ));
  }
}
