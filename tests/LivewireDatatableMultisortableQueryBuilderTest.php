<?php

namespace Mediconesystems\LivewireDatatables\Tests;

use Illuminate\Contracts\Session\Session;
use Mediconesystems\LivewireDatatables\Http\Livewire\LivewireDatatable;
use Mediconesystems\LivewireDatatables\Tests\Models\DummyBelongsToManyModel;
use Mediconesystems\LivewireDatatables\Tests\Models\DummyHasManyModel;
use Mediconesystems\LivewireDatatables\Tests\Models\DummyHasOneModel;
use Mediconesystems\LivewireDatatables\Tests\Models\DummyModel;

class LivewireDatatableMultisortQueryBuilderTest extends TestCase
{
    /** @test */
    public function it_toggles_sort_status_on_each_sort_trigger()
    {
        factory(DummyModel::class)->create();

        $subject = new LivewireDatatable(1);
        $subject->multisort = true;
        $subject->mount(DummyModel::class, ['id', 'subject', 'category']);

        $this->assertEquals('select "dummy_models"."id" as "id", "dummy_models"."subject" as "subject", "dummy_models"."category" as "category" from "dummy_models" order by `id` desc', $subject->getQuery()->toSql());

        $subject->sort(0);

        $this->assertEquals('select "dummy_models"."id" as "id", "dummy_models"."subject" as "subject", "dummy_models"."category" as "category" from "dummy_models" order by `id` asc', $subject->getQuery()->toSql());

        $subject->sort(0);

        $this->assertEquals('select "dummy_models"."id" as "id", "dummy_models"."subject" as "subject", "dummy_models"."category" as "category" from "dummy_models"', $subject->getQuery()->toSql());

        $subject->sort(0);

        $this->assertEquals('select "dummy_models"."id" as "id", "dummy_models"."subject" as "subject", "dummy_models"."category" as "category" from "dummy_models" order by `id` desc', $subject->getQuery()->toSql());

    }

    /** @test */
    public function it_creates_a_multisort_query_builder_for_base_columns()
    {
        factory(DummyModel::class)->create();
        $subject = new LivewireDatatable(1);
        $subject->multisort = true;
        $subject->mount(DummyModel::class, ['id', 'subject', 'category']);

        $this->assertEquals('select "dummy_models"."id" as "id", "dummy_models"."subject" as "subject", "dummy_models"."category" as "category" from "dummy_models" order by `id` desc', $subject->getQuery()->toSql());

        $subject->sort(1);

        $this->assertEquals('select "dummy_models"."id" as "id", "dummy_models"."subject" as "subject", "dummy_models"."category" as "category" from "dummy_models" order by `id` desc, `subject` desc', $subject->getQuery()->toSql());

        $subject->sort(1);

        $this->assertEquals('select "dummy_models"."id" as "id", "dummy_models"."subject" as "subject", "dummy_models"."category" as "category" from "dummy_models" order by `id` desc, `subject` asc', $subject->getQuery()->toSql());

        $subject->sort(1);

        $this->assertEquals('select "dummy_models"."id" as "id", "dummy_models"."subject" as "subject", "dummy_models"."category" as "category" from "dummy_models" order by `id` desc', $subject->getQuery()->toSql());

        $subject->sort(2);

        $this->assertEquals('select "dummy_models"."id" as "id", "dummy_models"."subject" as "subject", "dummy_models"."category" as "category" from "dummy_models" order by `id` desc, `category` desc', $subject->getQuery()->toSql());

        $subject->sort(1);

        $this->assertEquals('select "dummy_models"."id" as "id", "dummy_models"."subject" as "subject", "dummy_models"."category" as "category" from "dummy_models" order by `id` desc, `category` desc, `subject` desc', $subject->getQuery()->toSql());
    }

    /** @test */
    public function it_creates_a_multisort_query_builder_for_has_one_relation_columns()
    {
        factory(DummyModel::class)->create()->dummy_has_one()->save(factory(DummyHasOneModel::class)->make());

        $subject = new LivewireDatatable(1);
        $subject->multisort = true;
        $subject->mount(DummyModel::class, ['id', 'dummy_has_one.name', 'dummy_has_one.category']);

        $this->assertEquals('select "dummy_models"."id" as "id", "dummy_has_one_models"."name" as "dummy_has_one.name", "dummy_has_one_models"."category" as "dummy_has_one.category" from "dummy_models" left join "dummy_has_one_models" on "dummy_has_one_models"."dummy_model_id" = "dummy_models"."id" order by `id` desc', $subject->getQuery()->toSql());

        $subject->sort(1);
        $subject->forgetComputed();
        $this->assertEquals('select "dummy_models"."id" as "id", "dummy_has_one_models"."name" as "dummy_has_one.name", "dummy_has_one_models"."category" as "dummy_has_one.category" from "dummy_models" left join "dummy_has_one_models" on "dummy_has_one_models"."dummy_model_id" = "dummy_models"."id" order by dummy_models.id desc, dummy_has_one_models.name desc', $subject->getQuery()->toSql());

        $subject->sort(1);
        $subject->forgetComputed();
        $this->assertEquals('select "dummy_models"."id" as "id", "dummy_has_one_models"."name" as "dummy_has_one.name", "dummy_has_one_models"."category" as "dummy_has_one.category" from "dummy_models" left join "dummy_has_one_models" on "dummy_has_one_models"."dummy_model_id" = "dummy_models"."id" order by dummy_models.id desc, dummy_has_one_models.name asc', $subject->getQuery()->toSql());

        $subject->sort(2);
        $subject->forgetComputed();
        $this->assertEquals('select "dummy_models"."id" as "id", "dummy_has_one_models"."name" as "dummy_has_one.name", "dummy_has_one_models"."category" as "dummy_has_one.category" from "dummy_models" left join "dummy_has_one_models" on "dummy_has_one_models"."dummy_model_id" = "dummy_models"."id" order by dummy_models.id desc, dummy_has_one_models.name asc, dummy_has_one_models.category desc', $subject->getQuery()->toSql());
    }

    /** @test */
    public function it_creates_a_multisort_query_builder_for_has_many_relation_columns()
    {
        factory(DummyModel::class)->create()->dummy_has_many()->saveMany(factory(DummyHasManyModel::class, 2)->make());

        $subject = new LivewireDatatable(1);
        $subject->multisort = true;
        $subject->mount(DummyModel::class, ['id', 'dummy_has_many.name']);

        $this->assertEquals('select (select group_concat(REPLACE(DISTINCT(dummy_has_many_models.name), \'\', \'\') , \', \') from "dummy_has_many_models" where "dummy_models"."id" = "dummy_has_many_models"."dummy_model_id") as `dummy_has_many.name`, "dummy_models"."id" as "id" from "dummy_models" order by `id` desc', $subject->getQuery()->toSql());

        $subject->sort(1);

        $this->assertEquals('select (select group_concat(REPLACE(DISTINCT(dummy_has_many_models.name), \'\', \'\') , \', \') from "dummy_has_many_models" where "dummy_models"."id" = "dummy_has_many_models"."dummy_model_id") as `dummy_has_many.name`, "dummy_models"."id" as "id" from "dummy_models" order by `id` desc, `dummy_has_many.name` desc', $subject->getQuery()->toSql());

        $subject->sort(1);

        $this->assertEquals('select (select group_concat(REPLACE(DISTINCT(dummy_has_many_models.name), \'\', \'\') , \', \') from "dummy_has_many_models" where "dummy_models"."id" = "dummy_has_many_models"."dummy_model_id") as `dummy_has_many.name`, "dummy_models"."id" as "id" from "dummy_models" order by `id` desc, `dummy_has_many.name` asc', $subject->getQuery()->toSql());

        $subject->sort(0);

        $this->assertEquals('select (select group_concat(REPLACE(DISTINCT(dummy_has_many_models.name), \'\', \'\') , \', \') from "dummy_has_many_models" where "dummy_models"."id" = "dummy_has_many_models"."dummy_model_id") as `dummy_has_many.name`, "dummy_models"."id" as "id" from "dummy_models" order by `dummy_has_many.name` asc, `id` asc', $subject->getQuery()->toSql());
    }

    /** @test */
    public function it_creates_a_multisort_query_builder_for_has_many_relation_column_with_specific_aggregate()
    {
        factory(DummyModel::class)->create()->dummy_has_many()->saveMany(factory(DummyHasManyModel::class, 2)->make());

        $subject = new LivewireDatatable(1);
        $subject->multisort = true;
        $subject->mount(DummyModel::class, ['id', 'dummy_has_many.id:avg']);

        $this->assertEquals('select (select COALESCE(avg(dummy_has_many_models.id),0) from "dummy_has_many_models" where "dummy_models"."id" = "dummy_has_many_models"."dummy_model_id") as `dummy_has_many.id:avg`, "dummy_models"."id" as "id" from "dummy_models" order by `id` desc', $subject->getQuery()->toSql());

        $subject->sort(1);

        $this->assertEquals('select (select COALESCE(avg(dummy_has_many_models.id),0) from "dummy_has_many_models" where "dummy_models"."id" = "dummy_has_many_models"."dummy_model_id") as `dummy_has_many.id:avg`, "dummy_models"."id" as "id" from "dummy_models" order by `id` desc, `dummy_has_many.id:avg` desc', $subject->getQuery()->toSql());

        $subject->sort(1);

        $this->assertEquals('select (select COALESCE(avg(dummy_has_many_models.id),0) from "dummy_has_many_models" where "dummy_models"."id" = "dummy_has_many_models"."dummy_model_id") as `dummy_has_many.id:avg`, "dummy_models"."id" as "id" from "dummy_models" order by `id` desc, `dummy_has_many.id:avg` asc', $subject->getQuery()->toSql());
    }

    /** @test */
    public function it_creates_a_multisort_query_builder_for_belongs_to_relation_columns()
    {
        factory(DummyModel::class)->create()->dummy_has_many()->saveMany(factory(DummyHasManyModel::class, 2)->make());

        $subject = new LivewireDatatable(1);
        $subject->multisort = true;
        $subject->mount(DummyHasManyModel::class, ['id', 'dummy_model.name']);

        $this->assertEquals('select "dummy_has_many_models"."id" as "id", "dummy_models"."name" as "dummy_model.name" from "dummy_has_many_models" left join "dummy_models" on "dummy_has_many_models"."dummy_model_id" = "dummy_models"."id" order by `id` desc', $subject->getQuery()->toSql());

        $subject->sort(1);
        $subject->forgetComputed();

        $this->assertEquals('select "dummy_has_many_models"."id" as "id", "dummy_models"."name" as "dummy_model.name" from "dummy_has_many_models" left join "dummy_models" on "dummy_has_many_models"."dummy_model_id" = "dummy_models"."id" order by dummy_has_many_models.id desc, dummy_models.name desc', $subject->getQuery()->toSql());

        $subject->sort(1);
        $subject->forgetComputed();

        $this->assertEquals('select "dummy_has_many_models"."id" as "id", "dummy_models"."name" as "dummy_model.name" from "dummy_has_many_models" left join "dummy_models" on "dummy_has_many_models"."dummy_model_id" = "dummy_models"."id" order by dummy_has_many_models.id desc, dummy_models.name asc', $subject->getQuery()->toSql());

        $subject->sort(0);
        $subject->forgetComputed();

        $this->assertEquals('select "dummy_has_many_models"."id" as "id", "dummy_models"."name" as "dummy_model.name" from "dummy_has_many_models" left join "dummy_models" on "dummy_has_many_models"."dummy_model_id" = "dummy_models"."id" order by dummy_models.name asc, dummy_has_many_models.id asc', $subject->getQuery()->toSql());
    }

    /** @test */
    public function it_creates_a_multisort_query_builder_for_belongs_to_many_relation_columns()
    {
        factory(DummyModel::class)->create()->dummy_belongs_to_many()->attach(factory(DummyBelongsToManyModel::class)->create());

        $subject = new LivewireDatatable(1);
        $subject->multisort = true;
        $subject->mount(DummyModel::class, ['id', 'dummy_belongs_to_many.name']);

        $this->assertEquals('select (select group_concat(REPLACE(DISTINCT(dummy_belongs_to_many_models.name), \'\', \'\') , \', \') from "dummy_belongs_to_many_models" inner join "dummy_belongs_to_many_model_dummy_model" on "dummy_belongs_to_many_models"."id" = "dummy_belongs_to_many_model_dummy_model"."dummy_belongs_to_many_model_id" where "dummy_models"."id" = "dummy_belongs_to_many_model_dummy_model"."dummy_model_id" and "dummy_belongs_to_many_models"."deleted_at" is null) as `dummy_belongs_to_many.name`, "dummy_models"."id" as "id" from "dummy_models" order by `id` desc', $subject->getQuery()->toSql());

        $subject->sort(1);

        $this->assertEquals('select (select group_concat(REPLACE(DISTINCT(dummy_belongs_to_many_models.name), \'\', \'\') , \', \') from "dummy_belongs_to_many_models" inner join "dummy_belongs_to_many_model_dummy_model" on "dummy_belongs_to_many_models"."id" = "dummy_belongs_to_many_model_dummy_model"."dummy_belongs_to_many_model_id" where "dummy_models"."id" = "dummy_belongs_to_many_model_dummy_model"."dummy_model_id" and "dummy_belongs_to_many_models"."deleted_at" is null) as `dummy_belongs_to_many.name`, "dummy_models"."id" as "id" from "dummy_models" order by `id` desc, `dummy_belongs_to_many.name` desc', $subject->getQuery()->toSql());

        $subject->sort(1);

        $this->assertEquals('select (select group_concat(REPLACE(DISTINCT(dummy_belongs_to_many_models.name), \'\', \'\') , \', \') from "dummy_belongs_to_many_models" inner join "dummy_belongs_to_many_model_dummy_model" on "dummy_belongs_to_many_models"."id" = "dummy_belongs_to_many_model_dummy_model"."dummy_belongs_to_many_model_id" where "dummy_models"."id" = "dummy_belongs_to_many_model_dummy_model"."dummy_model_id" and "dummy_belongs_to_many_models"."deleted_at" is null) as `dummy_belongs_to_many.name`, "dummy_models"."id" as "id" from "dummy_models" order by `id` desc, `dummy_belongs_to_many.name` asc', $subject->getQuery()->toSql());

        $subject->sort(0);

        $this->assertEquals('select (select group_concat(REPLACE(DISTINCT(dummy_belongs_to_many_models.name), \'\', \'\') , \', \') from "dummy_belongs_to_many_models" inner join "dummy_belongs_to_many_model_dummy_model" on "dummy_belongs_to_many_models"."id" = "dummy_belongs_to_many_model_dummy_model"."dummy_belongs_to_many_model_id" where "dummy_models"."id" = "dummy_belongs_to_many_model_dummy_model"."dummy_model_id" and "dummy_belongs_to_many_models"."deleted_at" is null) as `dummy_belongs_to_many.name`, "dummy_models"."id" as "id" from "dummy_models" order by `dummy_belongs_to_many.name` asc, `id` asc', $subject->getQuery()->toSql());
    }
}
