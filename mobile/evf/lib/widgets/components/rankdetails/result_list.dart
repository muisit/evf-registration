import 'package:auto_size_text/auto_size_text.dart';
import 'package:evf/environment.dart';
import 'package:evf/l10n/categories.dart';
import 'package:evf/models/rank_details.dart';
import 'package:evf/models/ranking.dart';
import 'package:evf/models/ranking_position.dart';
import 'package:evf/models/result.dart';
import 'package:evf/styles.dart';
import 'package:flutter/material.dart';

import 'result_component.dart';
import 'result_header.dart';

class ResultList extends StatelessWidget {
  final RankDetails details;

  const ResultList({super.key, required this.details});

  @override
  Widget build(BuildContext context) {
    return ListView(children: _createRows(context, details.results));
  }

  List<Widget> _createRows(BuildContext context, List<Result> results) {
    List<Widget> retval = [const ResultHeader()];
    for (var i = 0; i < results.length; i++) {
      final Result result = results[i];

      retval.add(ResultComponent(result: result));
    }
    return retval;
  }
}
