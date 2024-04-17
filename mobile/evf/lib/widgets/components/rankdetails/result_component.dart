import 'package:evf/models/result.dart';
import 'package:evf/styles.dart';
import 'package:flutter/material.dart';

import 'result_event.dart';
import 'result_points.dart';

class ResultComponent extends StatelessWidget {
  final Result result;
  const ResultComponent({super.key, required this.result});

  @override
  Widget build(BuildContext context) {
    return Ink(
        color: result.status == 'Y' ? AppStyles.resultIncluded : AppStyles.resultExcluded,
        child: Column(
          children: [
            ResultEvent(result: result),
            ResultPoints(result: result),
          ],
        ));
  }
}
