<?php

namespace ZakharovAndrew\poll\models;

use Yii;
use yii\data\ActiveDataProvider;

class PollSearch extends Poll
{
    public function rules()
    {
        return [
            [['id', 'category_id', 'status', 'priority', 'created_by'], 'integer'],
            [['question', 'start_date', 'end_date', 'created_at'], 'safe'],
        ];
    }

    public function search($params)
    {
        $query = Poll::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => ['defaultOrder' => ['id' => SORT_DESC]],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        $query->andFilterWhere([
            'id' => $this->id,
            'category_id' => $this->category_id,
            'status' => $this->status,
            'priority' => $this->priority,
            'created_by' => $this->created_by,
        ]);

        $query->andFilterWhere(['like', 'question', $this->question])
              ->andFilterWhere(['>=', 'start_date', $this->start_date])
              ->andFilterWhere(['<=', 'end_date', $this->end_date]);

        return $dataProvider;
    }
}
