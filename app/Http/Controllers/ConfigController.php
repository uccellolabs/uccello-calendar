<?php

namespace Uccello\Calendar\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Uccello\Core\Http\Controllers\Core\Controller;
use Uccello\Core\Models\Domain;
use Uccello\Core\Models\Module;
use Uccello\Core\Models\Field;
use Uccello\Calendar\CalendarConfig;

class ConfigController extends Controller
{
    private $rules_nb = 4;


    /**
     * Check user permissions
     */
    protected function checkPermissions()
    {
        $this->middleware('uccello.permissions:admin');
    }

    /**
     * Load datas for config page
     *
     * @param Domain|null $domain
     * @param Module $module
     * @param Request $request
     * @return void
     */
    public function setup(?Domain $domain, Module $module, Request $request)
    {
        // Pre-process
        $this->preProcess($domain, $module, $request);

        $fields = [];
        $rules = [];


        //Load all fields available on CRM
        foreach(Field::all() as $module_field)
        {
            $field = new \StdClass;
            $field->name = $module_field->name;
            $field->id = $module_field->id;
            $field->module_id = $module_field->module_id;
            $fields[] = $field;
        }

        //Retrieve rule
        $db_rules = CalendarConfig::where('domain_id', $domain->id)->first();

        $cron_delay = 0;

        //Set cron delay
        if($db_rules->data!=NULL && array_key_exists('cron_delay', $db_rules->data))
            $cron_delay = intval($db_rules->data->cron_delay);
        if($cron_delay<1)
            $cron_delay = 5;
        //Get specific rules
        if($db_rules->data!=NULL)
        {
            for($i=0 ; $i<$this->rules_nb ; $i++)
            {
                if(property_exists($db_rules->data, 'rule_'.$i))
                {
                    $rules['module_'.$i] = $db_rules->data->{'rule_'.$i}->module;
                    $rules['field_'.$i] = $db_rules->data->{'rule_'.$i}->field;
                }
            }
        }

        $this->viewName = 'config.main';

        return $this->autoView([
            'fields' => $fields,
            'rules' => $rules,
            'rules_nb' => $this->rules_nb,
            'cron_delay' => $cron_delay,
        ]);
    }

    /**
     * Store config in database
     *
     * @param Domain|null $domain
     * @param Module $module
     * @param Request $request
     * @return void
     */
    public function saveConfig(?Domain $domain, Module $module, Request $request)
    {
        //Set cron delay if not set
        $cron_delay = intval($request->get('cron_delay'));
        if($cron_delay<1)
            $cron_delay = 5;

        $config = CalendarConfig::firstOrNew([
            'domain_id' => $domain->id,
            'user_id' => null,
            ]);
        //Fill in data's field
        $data = [];
        $data['cron_delay'] = $cron_delay;
        for($i=0 ; $i<intval($request->get('rules_nb')) ; $i++)
        {
            $rule = new \StdClass;
            $rule->module = $request->get('module_'.$i);
            $rule->field = $request->get('field_'.$i);
            $data['rule_'.$i] = $rule;
        }
        $config->data = $data;

        $config->save();

        return $config;
    }

    public function processAutomaticAssignment(?Domain $domain, Module $module, Request $request, $event)
    {
        //If the event needs to be updated
        if(strpos($event->title, ' - ')!=false &&
            ($event->entityType=="" || $event->entityId=="" || $event->entityType==null || $event->entityId==null))
        {
            //If the calendar is writable
            $calendar = (new \Uccello\Calendar\Http\Controllers\Generic\CalendarController)->retrieve($domain, $event->accountId ,$event->calendarId, $module, $request);
            if($calendar->read_only!=true)
            {

                //Get all rules
                $db_rules = CalendarConfig::where('domain_id', $domain->id)->first();
                $rules = [];
                for($i=0 ; $i<$this->rules_nb ; $i++)
                {
                    if(property_exists($db_rules->data, 'rule_'.$i))
                    {
                        $rule = new \StdClass;
                        $rule->module = $db_rules->data->{'rule_'.$i}->module;
                        $rule->field = $db_rules->data->{'rule_'.$i}->field;
                        if($rule->module!=null && $rule->field!=null)
                            $rules[] = $rule;
                    }
                }

                //We check, rule after rule, if the current rule can be resolved
                foreach($rules as $rule)
                {
                    $searched_value =  explode(' - ', $event->title)[0];
                    $entity_module = Module::where('id', $rule->module)->first();
                    $field = Field::where('id', $rule->field)->first();
                    $entityClass = new $entity_module->model_class;

                    //Search for this value in the database in the appropriate table and field
                    $result = $entityClass->where($field->name, $searched_value)->get();
                    $result_count = count($result);
                    if($result_count==1)
                    {
                        //If 1 row meet the requirements we update the event
                        $entity = $result->first();

                        $request = Request::create(ucroute('calendar.events.update', $domain, $module, ['type' => $calendar->service]),
                            'POST', array(
                                'domain'        => $domain,
                                'type'          => $calendar->service,
                                'module'        => $module,
                                'accountId'     => $event->accountId,
                                'calendarId'    => $calendar->id,
                                'id'            => $event->id,
                                'start_date'    => $event->start,
                                'end_date'      => $event->end,
                                'allDay'        => $event->allDay,
                                'subject'       => $event->title,
                                'location'      => $event->location,
                                'description'   => $event->description,
                                'entityType'    => $entity_module->name,
                                'entityId'      => $entity->id,
                        ));
                        $response = app()->handle($request);
                        break;
                    }
                    else if($result_count>1)
                    {
                        break;
                    }
                }
                //Else, no results were found
            }
        }
    }
}
