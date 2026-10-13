<?php

namespace IlBronza\Datatables\Tests;

use IlBronza\CRUD\Http\Controllers\Traits\StandardTraits\PackageStandardIndexTrait;
use IlBronza\CRUD\Http\Controllers\Traits\StandardTraits\PackageStandardQueryIndexTrait;
use IlBronza\CRUD\Traits\CRUDIndexTrait;
use IlBronza\Datatables\Datatables;
use IlBronza\Datatables\DatatablesServiceProvider;
use IlBronza\Datatables\Helpers\DatatableRowsLoader;
use Illuminate\Container\Container;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Facades\Schema;
use Orchestra\Testbench\TestCase;
use RuntimeException;

class DatatableRowRefreshTest extends TestCase
{
    private Container $previousContainer;
    private $previousFacadeApplication;

    protected function getPackageProviders($app)
    {
        return [DatatablesServiceProvider::class];
    }

    protected function defineEnvironment($app)
    {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '',
        ]);
    }

    protected function defineRoutes($router)
    {
        $router->get('row-refresh/legacy', fn (Request $request) => (new LegacyRowRefreshController())->run($request));
        $router->get('row-refresh/query', fn (Request $request) => (new QueryRowRefreshController())->run($request));
        $router->get('row-refresh/provided', fn (Request $request) => (new QueryRowRefreshController())->runProvided($request));
    }

    protected function setUp() : void
    {
        $this->previousContainer = Container::getInstance();
        $this->previousFacadeApplication = Facade::getFacadeApplication();
        parent::setUp();

        Schema::create('row_refresh_records', function (Blueprint $table)
        {
            $table->increments('id');
            $table->string('name');
            $table->boolean('active');
        });
        Schema::create('row_refresh_codes', function (Blueprint $table)
        {
            $table->string('code')->primary();
            $table->string('name');
        });

        DB::table('row_refresh_records')->insert([
            ['id' => 1, 'name' => 'First', 'active' => true],
            ['id' => 2, 'name' => 'Second', 'active' => true],
            ['id' => 3, 'name' => 'Hidden', 'active' => false],
        ]);
        DB::table('row_refresh_codes')->insert([
            ['code' => 'first-key', 'name' => 'First'],
            ['code' => 'second-key', 'name' => 'Second'],
        ]);

        RowRefreshRecord::$retrievedKeys = [];
        LegacyRowRefreshController::$loads = 0;
        QueryRowRefreshController::$queries = 0;
        DB::enableQueryLog();
        DB::flushQueryLog();
    }

    protected function tearDown() : void
    {
        try
        {
            parent::tearDown();
        }
        finally
        {
            Container::setInstance($this->previousContainer);
            Facade::setFacadeApplication($this->previousFacadeApplication);
        }
    }

    private function ajax(string $url)
    {
        return $this->getJson($url, ['X-Requested-With' => 'XMLHttpRequest']);
    }

    public function test_legacy_controller_filters_in_sql_before_hydrating_models_and_removes_the_live_scope() : void
    {
        $this->ajax('/row-refresh/legacy?rowId=2&ibeditor=true')
            ->assertOk()->assertJsonPath('data', [[2, 'Second']]);

        $this->assertSame([2], RowRefreshRecord::$retrievedKeys);
        $this->assertSame(1, LegacyRowRefreshController::$loads);
        $this->assertStringContainsString('"row_refresh_records"."id" in (2)', DB::getQueryLog()[0]['query']);
        $this->assertFalse(RowRefreshRecord::hasGlobalScope(DatatableRowsLoader::class));
        $this->assertSame([1, 2], RowRefreshRecord::query()->pluck('id')->all());
    }

    public function test_query_controller_uses_the_new_method_and_filters_without_a_live_model_scope() : void
    {
        $this->ajax('/row-refresh/query?rowId=2')->assertOk()->assertJsonPath('data', [[2, 'Second']]);

        $this->assertSame([2], RowRefreshRecord::$retrievedKeys);
        $this->assertSame(1, QueryRowRefreshController::$queries);
        $this->assertSame(0, LegacyRowRefreshController::$loads);
        $this->assertFalse(RowRefreshRecord::hasGlobalScope(DatatableRowsLoader::class));
    }

    public function test_normal_index_uses_the_same_query_contract_without_a_row_filter() : void
    {
        $this->ajax('/row-refresh/query')->assertOk()->assertJsonPath('data', [[1, 'First'], [2, 'Second']]);

        $this->assertSame([1, 2], RowRefreshRecord::$retrievedKeys);
        $this->assertSame(1, QueryRowRefreshController::$queries);
        $this->assertSame(0, LegacyRowRefreshController::$loads);
    }

    public function test_normal_legacy_index_still_uses_get_index_elements() : void
    {
        $this->ajax('/row-refresh/legacy')->assertOk()->assertJsonPath('data', [[1, 'First'], [2, 'Second']]);

        $this->assertSame(1, LegacyRowRefreshController::$loads);
        $this->assertFalse(RowRefreshRecord::hasGlobalScope(DatatableRowsLoader::class));
    }

    public function test_explicit_elements_getter_takes_precedence_over_the_controller_query_method() : void
    {
        $this->ajax('/row-refresh/provided?rowId=2')->assertOk()->assertJsonPath('data', [[2, 'Second']]);

        $this->assertSame(0, QueryRowRefreshController::$queries);
        $this->assertSame([2], RowRefreshRecord::$retrievedKeys);
    }

    public function test_selected_rows_are_filtered_in_sql_on_both_paths() : void
    {
        foreach (['legacy', 'query'] as $path)
        {
            RowRefreshRecord::$retrievedKeys = [];
            $this->ajax('/row-refresh/' . $path . '?rowIds[]=2&rowIds[]=3')
                ->assertOk()->assertJsonPath('data', [[2, 'Second']]);

            $this->assertSame([2], RowRefreshRecord::$retrievedKeys);
        }
    }

    public function test_missing_or_excluded_rows_return_an_empty_json_response() : void
    {
        foreach (['legacy', 'query'] as $path)
            foreach ([3, 999] as $id)
                $this->ajax('/row-refresh/' . $path . '?rowId=' . $id)
                    ->assertOk()->assertJsonPath('data', [])->assertJsonPath('recordsTotal', 0);

        $this->assertSame([], RowRefreshRecord::$retrievedKeys);
    }

    public function test_row_refresh_bypasses_the_full_table_debug_cache() : void
    {
        config(['datatables.debug' => true]);
        Auth::shouldReceive('id')->andReturn(42);
        Auth::shouldReceive('user')->andReturn(null);
        $cacheKey = 'rowrefresh42';
        cache()->put($cacheKey, [[1, 'Stale first'], [2, 'Stale second']], 300);

        $this->ajax('/row-refresh/legacy?rowId=2&cachedtablekey=' . $cacheKey)
            ->assertOk()->assertJsonPath('data', [[2, 'Second']]);

        $this->assertSame([2], RowRefreshRecord::$retrievedKeys);
        $this->assertTrue(cache()->has($cacheKey));
    }

    public function test_scope_cleanup_survives_an_exception_and_preserves_existing_scopes() : void
    {
        $originalScopes = (new RowRefreshRecord())->getGlobalScopes();

        try
        {
            DatatableRowsLoader::get(function ()
            {
                $this->assertTrue(RowRefreshRecord::hasGlobalScope(DatatableRowsLoader::class));
                throw new RuntimeException('Provider failed');
            }, RowRefreshRecord::class, [2]);
            $this->fail('The provider exception must propagate.');
        }
        catch (RuntimeException $exception)
        {
            $this->assertSame('Provider failed', $exception->getMessage());
        }

        $this->assertSame($originalScopes, (new RowRefreshRecord())->getGlobalScopes());
        $this->assertSame([1, 2], RowRefreshRecord::query()->pluck('id')->all());
    }

    public function test_scope_cleanup_keeps_scopes_of_models_booted_inside_the_provider() : void
    {
        RowRefreshCode::clearBootedModels();

        DatatableRowsLoader::get(function ()
        {
            new RowRefreshCode();
            return RowRefreshRecord::all();
        }, RowRefreshRecord::class, [2]);

        $this->assertTrue(RowRefreshCode::hasGlobalScope('named'));
        $this->assertFalse(RowRefreshRecord::hasGlobalScope(DatatableRowsLoader::class));
    }

    public function test_nested_row_loads_restore_the_outer_scope() : void
    {
        $rows = DatatableRowsLoader::get(function ()
        {
            $nested = DatatableRowsLoader::get(fn () => RowRefreshRecord::all(), RowRefreshRecord::class, [1]);
            $this->assertSame([1], $nested->modelKeys());

            return RowRefreshRecord::all();
        }, RowRefreshRecord::class, [2]);

        $this->assertSame([2], $rows->modelKeys());
        $this->assertFalse(RowRefreshRecord::hasGlobalScope(DatatableRowsLoader::class));
    }

    public function test_builders_retained_by_a_legacy_provider_do_not_keep_a_live_filter() : void
    {
        $retained = null;
        DatatableRowsLoader::get(function () use (&$retained)
        {
            $retained = RowRefreshRecord::query();
            return $retained->get();
        }, RowRefreshRecord::class, [2]);

        $this->assertSame([1, 2], $retained->pluck('id')->all());
    }

    public function test_both_paths_keep_existing_or_conditions_inside_the_id_filter() : void
    {
        $provider = fn () => RowRefreshRecord::query()->where('name', 'First')->orWhere('name', 'Second');

        $direct = DatatableRowsLoader::get(fn () => collect(), RowRefreshRecord::class, [2], $provider);
        $legacy = DatatableRowsLoader::get(fn () => $provider()->get(), RowRefreshRecord::class, [2]);

        $this->assertSame([2], $direct->modelKeys());
        $this->assertSame([2], $legacy->modelKeys());
        $this->assertSame([2, 2], RowRefreshRecord::$retrievedKeys);
    }

    public function test_custom_string_primary_keys_work_on_both_paths() : void
    {
        $provider = fn () => RowRefreshCode::query();
        $direct = DatatableRowsLoader::get(fn () => collect(), RowRefreshCode::class, ['second-key'], $provider);
        $legacy = DatatableRowsLoader::get(fn () => RowRefreshCode::all(), RowRefreshCode::class, ['second-key']);

        $this->assertSame(['second-key'], $direct->modelKeys());
        $this->assertSame(['second-key'], $legacy->modelKeys());
        $this->assertStringContainsString('"row_refresh_codes"."code" in (?)', DB::getQueryLog()[0]['query']);
    }

    public function test_empty_selection_does_not_execute_either_provider() : void
    {
        $provider = function () { $this->fail('An empty selection must not load rows.'); };

        $this->assertCount(0, DatatableRowsLoader::get($provider, RowRefreshRecord::class, []));
        $this->assertCount(0, DatatableRowsLoader::get($provider, RowRefreshRecord::class, [], $provider));
        $this->assertSame([], DB::getQueryLog());
    }

    public function test_preloaded_collections_and_non_closure_callables_remain_supported() : void
    {
        $controller = new LegacyRowRefreshController();
        $rows = DatatableRowsLoader::get([$controller, 'getIndexElements'], RowRefreshRecord::class, [2]);
        $this->assertSame([2], $rows->modelKeys());

        $preloaded = RowRefreshRecord::all();
        $rows = DatatableRowsLoader::get(fn () => $preloaded, RowRefreshRecord::class, [1]);
        $this->assertSame([1], $rows->modelKeys());
    }

    public function test_builder_returned_by_the_legacy_method_is_supported() : void
    {
        $rows = DatatableRowsLoader::get(fn () => RowRefreshRecord::query(), RowRefreshRecord::class, [2]);

        $this->assertSame([2], $rows->modelKeys());
        $this->assertSame([2], RowRefreshRecord::$retrievedKeys);
    }

    public function test_original_query_builder_is_not_modified_by_a_row_refresh() : void
    {
        $query = RowRefreshRecord::query();
        DatatableRowsLoader::get(fn () => collect(), RowRefreshRecord::class, [2], fn () => $query);

        $this->assertSame([1, 2], $query->pluck('id')->all());
    }

    public function test_standard_query_trait_is_opt_in_and_keeps_standard_scopes_and_customization() : void
    {
        $legacy = new StandardLegacyRowRefreshController();
        $query = new StandardQueryRowRefreshController();

        $this->assertFalse(method_exists($legacy, 'getIndexQuery'));
        $this->assertTrue(method_exists($query, 'getIndexQuery'));
        $this->assertSame([2], $legacy->getIndexElements()->modelKeys());
        $this->assertSame([2], $query->getIndexQuery()->get()->modelKeys());
        $this->assertSame([2], $query->getIndexElements()->modelKeys());
    }

    public function test_standalone_tables_can_use_a_query_provider() : void
    {
        $table = Datatables::createStandAloneTable([
            'name' => 'standalone-rows',
            'fieldsGroups' => ['index' => ['fields' => ['id' => 'primary', 'name' => 'flat']]],
            'elements' => function () { $this->fail('The query provider must take precedence.'); },
            'modelClass' => RowRefreshRecord::class,
            'queryProvider' => fn () => RowRefreshRecord::query()->second(),
        ]);

        $this->assertSame([[2, 'Second']], $table->calculateData());
        $this->assertFalse(property_exists($table, 'queryProvider'));
    }
}

class RowRefreshRecord extends Model
{
    protected $table = 'row_refresh_records';
    public $timestamps = false;
    public static array $retrievedKeys = [];

    protected static function booted() : void
    {
        static::addGlobalScope('active', fn (Builder $query) => $query->where('active', true));
        static::retrieved(function (self $model) { static::$retrievedKeys[] = $model->getKey(); });
    }

    public function scopeSecond(Builder $query) : void
    {
        $query->where('name', 'Second');
    }
}

class RowRefreshCode extends Model
{
    protected $table = 'row_refresh_codes';
    protected $primaryKey = 'code';
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;

    protected static function booted() : void
    {
        static::addGlobalScope('named', fn (Builder $query) => $query->whereNotNull('name'));
    }
}

class LegacyRowRefreshController extends \Illuminate\Routing\Controller
{
    use CRUDIndexTrait;

    public bool $useIndexCache = false;
    public bool $rowSelectCheckboxes = false;
    public array $indexFieldsGroups = ['index'];
    public static int $loads = 0;
    public Datatables $table;

    public function getModelClass() : string { return RowRefreshRecord::class; }
    public function setPagetitle() {}
    public function getTableFieldsGroups(string|array $keys)
    {
        return ['index' => ['fields' => ['id' => 'primary', 'name' => 'flat']]];
    }

    public function getIndexElements()
    {
        static::$loads++;
        return RowRefreshRecord::all();
    }

    public function run(Request $request)
    {
        return $this->_index($request, 'row-refresh', ['index']);
    }

    public function runProvided(Request $request)
    {
        return $this->_index($request, 'row-refresh', ['index'], fn () => RowRefreshRecord::all());
    }
}

class QueryRowRefreshController extends LegacyRowRefreshController
{
    public static int $queries = 0;

    public function getIndexQuery() : Builder
    {
        if (RowRefreshRecord::hasGlobalScope(DatatableRowsLoader::class))
            throw new RuntimeException('The query contract must not register a live model scope.');

        static::$queries++;
        return RowRefreshRecord::query();
    }

    public function getIndexElements()
    {
        throw new RuntimeException('The query contract must bypass the legacy method.');
    }
}

class StandardLegacyRowRefreshController
{
    use PackageStandardIndexTrait;

    public function getModelClass() : string { return RowRefreshRecord::class; }
    public function getIndexElementsScopesArray() : array { return ['second']; }
    public function customizeQuery($query) { return $query->where('id', '>', 1); }
}

class StandardQueryRowRefreshController extends StandardLegacyRowRefreshController
{
    use PackageStandardQueryIndexTrait;

    // Retain the parent's customization when importing the standard query trait.
    public function getIndexElementsScopesArray() : array { return ['second']; }
    public function customizeQuery($query) { return $query->where('id', '>', 1); }
}
