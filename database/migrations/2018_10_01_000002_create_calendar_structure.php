<?php

use Illuminate\Database\Migrations\Migration;
use Uccello\Core\Database\Migrations\Traits\TablePrefixTrait;
use Uccello\Core\Models\Block;
use Uccello\Core\Models\Module;
use Uccello\Core\Models\Domain;
use Uccello\Core\Models\Field;
use Uccello\Core\Models\Filter;
use Uccello\Core\Models\Tab;

class CreateCalendarStructure extends Migration
{
    use TablePrefixTrait;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $module = $this->createModule();
        $this->activateModuleOnDomains($module);
        $this->createTabsBlocksFields($module);
        $this->createFilters($module);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Module::where('name', 'calendar')->forceDelete();
    }

    protected function createModule()
    {
        $module = new  Module();
        $module->name = 'calendar';
        $module->icon = 'date_range';
        $module->model_class = null;
        $module->data = ["package" => "uccello/calendar", "menu" => 'uccello.index'];
        $module->save();

        return $module;
    }

    protected function activateModuleOnDomains($module)
    {
        $domains = Domain::all();
        foreach ($domains as $domain) {
            $domain->modules()->attach($module);
        }
    }

    protected function createTabsBlocksFields($module)
    {
        // Tab tab.main
        $tab = new Tab([
            'module_id' => $module->id,
            'label' => 'tab.main',
            'icon' => null,
            'sequence' => 0,
            'data' => null
        ]);
        $tab->save();

        // Block block.general
        $block = new Block([
            'module_id' => $module->id,
            'tab_id' => $tab->id,
            'label' => 'block.general',
            'icon' => 'info',
            'sequence' => 0,
            'data' => null
        ]);
        $block->save();

        // Field subject
        $field = new Field([
            'module_id' => $module->id,
            'block_id' => $block->id,
            'name' => 'subject',
            'uitype_id' => uitype('text')->id,
            'displaytype_id' => displaytype('everywhere')->id,
            'sequence' => 0,
            'data' => json_decode('{"rules":"required","default":"Appel"}')
        ]);
        $field->save();

        // Field category
        $field = new Field([
            'module_id' => $module->id,
            'block_id' => $block->id,
            'name' => 'category',
            'uitype_id' => uitype('text')->id,
            'displaytype_id' => displaytype('everywhere')->id,
            'sequence' => 0,
            'data' => null
        ]);
        $field->save();

        // Field start_date
        $field = new Field([
            'module_id' => $module->id,
            'block_id' => $block->id,
            'name' => 'start_date',
            'uitype_id' => uitype('datetime')->id,
            'displaytype_id' => displaytype('everywhere')->id,
            'sequence' => 1,
            'data' => json_decode('{"rules":"required"}')
        ]);
        $field->save();

        // Field end_date
        $field = new Field([
            'module_id' => $module->id,
            'block_id' => $block->id,
            'name' => 'end_date',
            'uitype_id' => uitype('datetime')->id,
            'displaytype_id' => displaytype('everywhere')->id,
            'sequence' => 2,
            'data' => json_decode('{"rules":"required"}')
        ]);
        $field->save();

        // Field location
        $field = new Field([
            'module_id' => $module->id,
            'block_id' => $block->id,
            'name' => 'location',
            'uitype_id' => uitype('text')->id,
            'displaytype_id' => displaytype('everywhere')->id,
            'sequence' => 3,
            'data' => null
        ]);
        $field->save();

        // Field calendar
        $field = new Field([
            'module_id' => $module->id,
            'block_id' => $block->id,
            'name' => 'calendar',
            'uitype_id' => uitype('text')->id,
            'displaytype_id' => displaytype('everywhere')->id,
            'sequence' => 4,
            'data' => json_decode('{"rules":"required"}')
        ]);
        $field->save();

        // Field description
        $field = new Field([
            'module_id' => $module->id,
            'block_id' => $block->id,
            'name' => 'description',
            'uitype_id' => uitype('textarea')->id,
            'displaytype_id' => displaytype('everywhere')->id,
            'sequence' => 5,
            'data' => json_decode('{"large":true}')
        ]);
        $field->save();
    }

    protected function createFilters($module)
    {
        // Filter
        $filter = new Filter([
            'module_id' => $module->id,
            'domain_id' => null,
            'user_id' => null,
            'name' => 'filter.all',
            'type' => 'list',
            'columns' => ['subject', 'start_date', 'end_date', 'category', 'location', 'calendar'],
            'conditions' => null,
            'order_by' => null,
            'is_default' => true,
            'is_public' => false
        ]);
        $filter->save();

        $filter = new Filter([
            'module_id' => $module->id,
            'domain_id' => null,
            'user_id' => null,
            'name' => 'filter.related-list',
            'type' => 'related-list',
            'columns' => ['subject', 'start_date', 'end_date', 'category', 'calendar'],
            'conditions' => null,
            'order_by' => null,
            'is_default' => true,
            'is_public' => false
        ]);
        $filter->save();
    }
}
