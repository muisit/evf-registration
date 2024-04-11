import 'package:flutter/material.dart';
import 'package:flutter_gen/gen_l10n/app_localizations.dart';

String translateCategory(BuildContext context, String category) {
  switch (category) {
    case 'Cat 1':
      return AppLocalizations.of(context)!.labelCategoryCat1;
    case 'Cat 2':
      return AppLocalizations.of(context)!.labelCategoryCat2;
    case 'Cat 3':
      return AppLocalizations.of(context)!.labelCategoryCat3;
    case 'Cat 4':
      return AppLocalizations.of(context)!.labelCategoryCat4;
  }
  return AppLocalizations.of(context)!.labelCategoryCat1;
}
