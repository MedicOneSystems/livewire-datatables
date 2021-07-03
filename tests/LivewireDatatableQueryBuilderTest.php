<?php

namespace Mediconesystems\LivewireDatatables\Tests;

use Mediconesystems\LivewireDatatables\Http\Livewire\LivewireDatatable;
use Mediconesystems\LivewireDatatables\Tests\Models\DummyBelongsToManyModel;
use Mediconesystems\LivewireDatatables\Tests\Models\DummyHasManyModel;
use Mediconesystems\LivewireDatatables\Tests\Models\DummyHasOneModel;
use Mediconesystems\LivewireDatatables\Tests\Models\DummyModel;

class LivewireDatatableQueryBuilderTest extends TestCase
{
    /** @test */
    public function it_creates_a_query_builder_for_base_columns()
    {
        factory(DummyModel::class)->create();

        $subject = new LivewireDatatable(1);
        $subject->mount(DummyModel::class, ['id', 'subject']);

        $this->assertEquals('select "dummy_models"."id" as "id", "dummy_models"."subject" as "subject" from "dummy_models" order by `id` desc', $subject->getQuery()->toSql());

        $subject->sort(1);

        $this->assertEquals('select "dummy_models"."id" as "id", "dummy_models"."subject" as "subject" from "dummy_models" order by `subject` desc', $subject->getQuery()->toSql());

        $subject->sort(1);

        $this->assertEquals('select "dummy_models"."id" as "id", "dummy_models"."subject" as "subject" from "dummy_models" order by `subject` asc', $subject->getQuery()->toSql());
    }

    /** @test */
    public function it_creates_a_query_builder_for_has_one_relation_columns()
    {
        factory(DummyModel::class)->create()->dummy_has_one()->save(factory(DummyHasOneModel::class)->make());

        $subject = new LivewireDatatable(1);
        $subject->mount(DummyModel::class, ['id', 'dummy_has_one.name']);

        $this->assertEquals('select "dummy_models"."id" as "id", "dummy_has_one_models"."name" as "dummy_has_one.name" from "dummy_models" left join "dummy_has_one_models" on "dummy_has_one_models"."dummy_model_id" = "dummy_models"."id" order by `id` desc', $subject->getQuery()->toSql());

        $subject->sort(1);
        $subject->forgetComputed();

        $this->assertEquals('select "dummy_models"."id" as "id", "dummy_has_one_models"."name" as "dummy_has_one.name" from "dummy_models" left join "dummy_has_one_models" on "dummy_has_one_models"."dummy_model_id" = "dummy_models"."id" order by dummy_has_one_models.name desc', $subject->getQuery()->toSql());

        $subject->sort(1);
        $subject->forgetComputed();

        $this->assertEquals('select "dummy_models"."id" as "id", "dummy_has_one_models"."name" as "dummy_has_one.name" from "dummy_models" left join "dummy_has_one_models" on "dummy_has_one_models"."dummy_model_id" = "dummy_models"."id" order by dummy_has_one_models.name asc', $subject->getQuery()->toSql());
    }

    /** @test */
    public function it_creates_a_where_query_for_a_has_one_column()
    {
        factory(DummyModel::class)->create()->dummy_has_one()->save(factory(DummyHasOneModel::class)->make());

        $subject = new LivewireDatatable(1);
        $subject->mount(DummyModel::class, ['id', 'dummy_has_one.name']);
        $subject->doSelectFilter(1, 'dwight');

        $this->assertEquals('select "dummy_models"."id" as "id", "dummy_has_one_models"."name" as "dummy_has_one.name" from "dummy_models" left join "dummy_has_one_models" on "dummy_has_one_models"."dummy_model_id" = "dummy_models"."id" where ((("dummy_has_one_models"."name" = ?))) order by `id` desc', $subject->getQuery()->toSql());
        $this->assertEquals(['dwight'], $subject->getQuery()->getBindings());
    }

    /** @test */
    public function it_creates_a_query_builder_for_has_many_relation_columns()
    {
        factory(DummyModel::class)->create()->dummy_has_many()->saveMany(factory(DummyHasManyModel::class, 2)->make());

        $subject = new LivewireDatatable(1);
        $subject->mount(DummyModel::class, ['id', 'dummy_has_many.name']);

        $this->assertEquals('select (select group_concat(REPLACE(DISTINCT(dummy_has_many_models.name), \'\', \'\') , \', \') from "dummy_has_many_models" where "dummy_models"."id" = "dummy_has_many_models"."dummy_model_id") as `dummy_has_many.name`, "dummy_models"."id" as "id" from "dummy_models" order by `id` desc', $subject->getQuery()->toSql());

        $subject->sort(1);

        $this->assertEquals('select (select group_concat(REPLACE(DISTINCT(dummy_has_many_models.name), \'\', \'\') , \', \') from "dummy_has_many_models" where "dummy_models"."id" = "dummy_has_many_models"."dummy_model_id") as `dummy_has_many.name`, "dummy_models"."id" as "id" from "dummy_models" order by `dummy_has_many.name` desc', $subject->getQuery()->toSql());

        $subject->sort(1);

        $this->assertEquals('select (select group_concat(REPLACE(DISTINCT(dummy_has_many_models.name), \'\', \'\') , \', \') from "dummy_has_many_models" where "dummy_models"."id" = "dummy_has_many_models"."dummy_model_id") as `dummy_has_many.name`, "dummy_models"."id" as "id" from "dummy_models" order by `dummy_has_many.name` asc', $subject->getQuery()->toSql());
    }

    /** @test */
    public function it_creates_a_query_builder_for_has_many_relation_column_with_specific_aggregate()
    {
        factory(DummyModel::class)->create()->dummy_has_many()->saveMany(factory(DummyHasManyModel::class, 2)->make());

        $subject = new LivewireDatatable(1);
        $subject->mount(DummyModel::class, ['id', 'dummy_has_many.id:avg']);

        $this->assertEquals('select (select COALESCE(avg(dummy_has_many_models.id),0) from "dummy_has_many_models" where "dummy_models"."id" = "dummy_has_many_models"."dummy_model_id") as `dummy_has_many.id:avg`, "dummy_models"."id" as "id" from "dummy_models" order by `id` desc', $subject->getQuery()->toSql());

        $subject->sort(1);

        $this->assertEquals('select (select COALESCE(avg(dummy_has_many_models.id),0) from "dummy_has_many_models" where "dummy_models"."id" = "dummy_has_many_models"."dummy_model_id") as `dummy_has_many.id:avg`, "dummy_models"."id" as "id" from "dummy_models" order by `dummy_has_many.id:avg` desc', $subject->getQuery()->toSql());

        $subject->sort(1);

        $this->assertEquals('select (select COALESCE(avg(dummy_has_many_models.id),0) from "dummy_has_many_models" where "dummy_models"."id" = "dummy_has_many_models"."dummy_model_id") as `dummy_has_many.id:avg`, "dummy_models"."id" as "id" from "dummy_models" order by `dummy_has_many.id:avg` asc', $subject->getQuery()->toSql());
    }

    /** @test */
    public function it_creates_a_where_query_for_has_many_relation_columns()
    {
        factory(DummyModel::class)->create()->dummy_has_many()->saveMany(factory(DummyHasManyModel::class, 2)->make());

        $subject = new LivewireDatatable(1);
        $subject->mount(DummyModel::class, ['id', 'dummy_has_many.name']);
        $subject->doTextFilter(1, 'Pam');

        $this->assertEquals('select (select group_concat(REPLACE(DISTINCT(dummy_has_many_models.name), \'\', \'\') , \', \') from "dummy_has_many_models" where "dummy_models"."id" = "dummy_has_many_models"."dummy_model_id") as `dummy_has_many.name`, "dummy_models"."id" as "id" from "dummy_models" where ((((select group_concat(REPLACE(DISTINCT(dummy_has_many_models.name), \'\', \'\') , \', \') from "dummy_has_many_models" where "dummy_models"."id" = "dummy_has_many_models"."dummy_model_id") like ?))) order by `id` desc', $subject->getQuery()->toSql());

        $this->assertEquals(['%Pam%'], $subject->getQuery()->getBindings());
    }

    /** @test */
    public function it_creates_a_query_builder_for_belongs_to_relation_columns()
    {
        factory(DummyModel::class)->create()->dummy_has_many()->saveMany(factory(DummyHasManyModel::class, 2)->make());

        $subject = new LivewireDatatable(1);
        $subject->mount(DummyHasManyModel::class, ['id', 'dummy_model.name']);

        $this->assertEquals('select "dummy_has_many_models"."id" as "id", "dummy_models"."name" as "dummy_model.name" from "dummy_has_many_models" left join "dummy_models" on "dummy_has_many_models"."dummy_model_id" = "dummy_models"."id" order by `id` desc', $subject->getQuery()->toSql());

        $subject->sort(1);
        $subject->forgetComputed();

        $this->assertEquals('select "dummy_has_many_models"."id" as "id", "dummy_models"."name" as "dummy_model.name" from "dummy_has_many_models" left join "dummy_models" on "dummy_has_many_models"."dummy_model_id" = "dummy_models"."id" order by dummy_models.name desc', $subject->getQuery()->toSql());

        $subject->sort(1);
        $subject->forgetComputed();

        $this->assertEquals('select "dummy_has_many_models"."id" as "id", "dummy_models"."name" as "dummy_model.name" from "dummy_has_many_models" left join "dummy_models" on "dummy_has_many_models"."dummy_model_id" = "dummy_models"."id" order by dummy_models.name asc', $subject->getQuery()->toSql());
    }

    /** @test */
    public function it_creates_a_where_query_for_belongs_to_relation_columns()
    {
        factory(DummyModel::class)->create()->dummy_has_many()->saveMany(factory(DummyHasManyModel::class, 2)->make());

        $subject = new LivewireDatatable(1);
        $subject->mount(DummyHasManyModel::class, ['id', 'dummy_model.name']);

        $subject->doNumberFilterStart(1, 123);
        // $subject->doNumberFilterEnd(1, 456);
        $subject->forgetComputed();

        $this->assertEquals('select "dummy_has_many_models"."id" as "id", "dummy_models"."name" as "dummy_model.name" from "dummy_has_many_models" left join "dummy_models" on "dummy_has_many_models"."dummy_model_id" = "dummy_models"."id" where (dummy_models.name >= ?) order by dummy_has_many_models.id desc', $subject->getQuery()->toSql());

        $this->assertEquals([123], $subject->getQuery()->getBindings());

        $subject->doNumberFilterEnd(1, 456);

        $this->assertEquals([123, 456], $subject->getQuery()->getBindings());

        $subject->doNumberFilterStart(1, null);

        $this->assertEquals([456], $subject->getQuery()->getBindings());

        $subject->doNumberFilterEnd(1, null);

        $subject->forgetComputed();
        $this->assertEquals('select "dummy_has_many_models"."id" as "id", "dummy_models"."name" as "dummy_model.name" from "dummy_has_many_models" left join "dummy_models" on "dummy_has_many_models"."dummy_model_id" = "dummy_models"."id" order by dummy_has_many_models.id desc', $subject->getQuery()->toSql());
        $this->assertEquals([], $subject->getQuery()->getBindings());
    }

    /** @test */
    public function it_creates_a_query_builder_for_belongs_to_many_relation_columns()
    {
        factory(DummyModel::class)->create()->dummy_belongs_to_many()->attach(factory(DummyBelongsToManyModel::class)->create());

        $subject = new LivewireDatatable(1);
        $subject->mount(DummyModel::class, ['id', 'dummy_belongs_to_many.name']);

        $this->assertEquals('select (select group_concat(REPLACE(DISTINCT(dummy_belongs_to_many_models.name), \'\', \'\') , \', \') from "dummy_belongs_to_many_models" inner join "dummy_belongs_to_many_model_dummy_model" on "dummy_belongs_to_many_models"."id" = "dummy_belongs_to_many_model_dummy_model"."dummy_belongs_to_many_model_id" where "dummy_models"."id" = "dummy_belongs_to_many_model_dummy_model"."dummy_model_id" and "dummy_belongs_to_many_models"."deleted_at" is null) as `dummy_belongs_to_many.name`, "dummy_models"."id" as "id" from "dummy_models" order by `id` desc', $subject->getQuery()->toSql());

        $subject->sort(1);

        $this->assertEquals('select (select group_concat(REPLACE(DISTINCT(dummy_belongs_to_many_models.name), \'\', \'\') , \', \') from "dummy_belongs_to_many_models" inner join "dummy_belongs_to_many_model_dummy_model" on "dummy_belongs_to_many_models"."id" = "dummy_belongs_to_many_model_dummy_model"."dummy_belongs_to_many_model_id" where "dummy_models"."id" = "dummy_belongs_to_many_model_dummy_model"."dummy_model_id" and "dummy_belongs_to_many_models"."deleted_at" is null) as `dummy_belongs_to_many.name`, "dummy_models"."id" as "id" from "dummy_models" order by `dummy_belongs_to_many.name` desc', $subject->getQuery()->toSql());

        $subject->sort(1);

        $this->assertEquals('select (select group_concat(REPLACE(DISTINCT(dummy_belongs_to_many_models.name), \'\', \'\') , \', \') from "dummy_belongs_to_many_models" inner join "dummy_belongs_to_many_model_dummy_model" on "dummy_belongs_to_many_models"."id" = "dummy_belongs_to_many_model_dummy_model"."dummy_belongs_to_many_model_id" where "dummy_models"."id" = "dummy_belongs_to_many_model_dummy_model"."dummy_model_id" and "dummy_belongs_to_many_models"."deleted_at" is null) as `dummy_belongs_to_many.name`, "dummy_models"."id" as "id" from "dummy_models" order by `dummy_belongs_to_many.name` asc', $subject->getQuery()->toSql());
    }

    /** @test */
    public function it_creates_a__where_query_for_belongs_to_many_relation_columns()
    {
        factory(DummyModel::class)->create()->dummy_belongs_to_many()->attach(factory(DummyBelongsToManyModel::class)->create());

        $subject = new LivewireDatatable(1);
        $subject->mount(DummyModel::class, ['id', 'dummy_belongs_to_many.name']);

        $subject->doSelectFilter(1, 'Michael Scott');

        $this->assertEquals('select (select group_concat(REPLACE(DISTINCT(dummy_belongs_to_many_models.name), \'\', \'\') , \', \') from "dummy_belongs_to_many_models" inner join "dummy_belongs_to_many_model_dummy_model" on "dummy_belongs_to_many_models"."id" = "dummy_belongs_to_many_model_dummy_model"."dummy_belongs_to_many_model_id" where "dummy_models"."id" = "dummy_belongs_to_many_model_dummy_model"."dummy_model_id" and "dummy_belongs_to_many_models"."deleted_at" is null) as `dummy_belongs_to_many.name`, "dummy_models"."id" as "id" from "dummy_models" where ((((select group_concat(REPLACE(DISTINCT(dummy_belongs_to_many_models.name), \'\', \'\') , \', \') from "dummy_belongs_to_many_models" inner join "dummy_belongs_to_many_model_dummy_model" on "dummy_belongs_to_many_models"."id" = "dummy_belongs_to_many_model_dummy_model"."dummy_belongs_to_many_model_id" where "dummy_models"."id" = "dummy_belongs_to_many_model_dummy_model"."dummy_model_id" and "dummy_belongs_to_many_models"."deleted_at" is null) like ?))) order by `id` desc', $subject->getQuery()->toSql());
    }
}
