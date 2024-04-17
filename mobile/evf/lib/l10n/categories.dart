import 'package:flutter/material.dart';
import 'package:flutter_gen/gen_l10n/app_localizations.dart';

String translateCategory(BuildContext context, String category) {
  switch (category) {
    case '1':
      return AppLocalizations.of(context)!.labelCategoryCat1;
    case '2':
      return AppLocalizations.of(context)!.labelCategoryCat2;
    case '3':
      return AppLocalizations.of(context)!.labelCategoryCat3;
    case '4':
      return AppLocalizations.of(context)!.labelCategoryCat4;
  }
  return AppLocalizations.of(context)!.labelCategoryCat1;
}
