import 'package:flutter/material.dart';
import 'package:flutter_gen/gen_l10n/app_localizations.dart';

String translateWeapons(BuildContext context, String weapon) {
  switch (weapon) {
    case 'Mens Foil':
      return AppLocalizations.of(context)!.labelWeaponMF;
    case 'Mens Epee':
      return AppLocalizations.of(context)!.labelWeaponME;
    case 'Mens Sabre':
      return AppLocalizations.of(context)!.labelWeaponMS;
    case 'Womens Foil':
      return AppLocalizations.of(context)!.labelWeaponWF;
    case 'Womens Epee':
      return AppLocalizations.of(context)!.labelWeaponWE;
    case 'Womens Sabre':
      return AppLocalizations.of(context)!.labelWeaponWS;
  }
  return AppLocalizations.of(context)!.labelWeaponMF;
}
