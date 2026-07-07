<?php

namespace ZakharovAndrew\poll\controllers;

use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use ZakharovAndrew\poll\models\PollCategory;

/**
 * Admin controller for managing poll categories.
 *
 * @author Andrew Zakharov
 * @since 1.0.0
 */
class CategoryController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['admin'], // Adjust to your RBAC role
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all categories.
     *
     * @return string
     */
    public function actionIndex()
    {
        $categories = PollCategory::find()
            ->orderBy(['sort_order' => SORT_ASC, 'name' => SORT_ASC])
            ->all();

        return $this->render('index', [
            'categories' => $categories,
        ]);
    }

    /**
     * Creates a new category.
     *
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new PollCategory();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Category created successfully.');
            return $this->redirect(['index']);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing category.
     *
     * @param int $id
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Category updated successfully.');
            return $this->redirect(['index']);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes a category.
     *
     * @param int $id
     * @return \yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $model->delete();
        Yii::$app->session->setFlash('success', 'Category deleted successfully.');
        return $this->redirect(['index']);
    }

    /**
     * Finds the PollCategory model based on its primary key value.
     *
     * @param int $id
     * @return PollCategory
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = PollCategory::findOne($id)) !== null) {
            return $model;
        }
        throw new NotFoundHttpException('The requested category does not exist.');
    }
}
