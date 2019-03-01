<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Uccello\Core\Database\Migrations\Migration;
use Uccello\Core\Models\Module;
use Uccello\Core\Models\Domain;
use Uccello\Core\Models\Tab;
use Uccello\Core\Models\Block;
use Uccello\Core\Models\Field;
use Uccello\Core\Models\Filter;
use Uccello\Core\Models\RelatedList;
use Uccello\Core\Models\Link;

class CreateTaskModule extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->createTable();
        $module = $this->createModule();
        $this->activateModuleOnDomains($module);
        $this->createTabsBlocksFields($module);
        $this->createFilters($module);
        $this->createRelatedLists($module);
        $this->createLinks($module);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Drop table
        Schema::dropIfExists($this->tablePrefix . 'tasks');

        // Delete module
        Module::where('name', 'task')->forceDelete();
    }

    protected function initTablePrefix()
    {
        $this->tablePrefix = 'calendar_';

        return $this->tablePrefix;
    }

    protected function createTable()
    {
        Schema::create($this->tablePrefix . 'tasks', function (Blueprint $table) {
            $table->increments('id');
            $table->string('subject');
            $table->datetime('datetime');
            $table->boolean('done')->nullable();
            $table->unsignedInteger('contact_id')->nullable();
            $table->unsignedInteger('assigned_user_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    protected function createModule()
    {
        $module = new Module([
            'name' => 'task',
            'icon' => 'playlist_add_check',
            'model_class' => 'Uccello\Calendar\Task',
            'data' => null
        ]);
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
            'data' => json_decode('{"rules":"required","default":"Relance"}')
        ]);
        $field->save();

        // Field datetime
        $field = new Field([
            'module_id' => $module->id,
            'block_id' => $block->id,
            'name' => 'datetime',
            'uitype_id' => uitype('datetime')->id,
            'displaytype_id' => displaytype('everywhere')->id,
            'sequence' => 1,
            'data' => json_decode('{"rules":"required"}')
        ]);
        $field->save();

        // Field done
        $field = new Field([
            'module_id' => $module->id,
            'block_id' => $block->id,
            'name' => 'done',
            'uitype_id' => uitype('boolean')->id,
            'displaytype_id' => displaytype('everywhere')->id,
            'sequence' => 3,
            'data' => null
        ]);
        $field->save();

        // Field contact
        $field = new Field([
            'module_id' => $module->id,
            'block_id' => $block->id,
            'name' => 'contact',
            'uitype_id' => uitype('entity')->id,
            'displaytype_id' => displaytype('everywhere')->id,
            'sequence' => 4,
            'data' => [ "rules" => "required", "module" => "contact" ]
        ]);
        $field->save();

        // Field assigned_user
        $field = new Field([
            'module_id' => $module->id,
            'block_id' => $block->id,
            'name' => 'assigned_user',
            'uitype_id' => uitype('entity')->id,
            'displaytype_id' => displaytype('everywhere')->id,
            'sequence' => 6,
            'data' => json_decode('{"module":"user"}')
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
            'columns' => ['subject', 'datetime', 'type', 'done', 'contact', 'assigned_user'],
            'conditions' => null,
            'order_by' => null,
            'is_default' => true,
            'is_public' => false
        ]);
        $filter->save();

    }

    protected function createRelatedLists($module)
    {
    }

    protected function createLinks($module)
    {
    }
}
