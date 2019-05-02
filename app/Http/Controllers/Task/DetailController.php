<?php

namespace App\Http\Controllers\Task;

use Illuminate\Http\Request;
use Uccello\Core\Http\Controllers\Core\Controller;
use Uccello\Core\Events\BeforeSaveEvent;
use Uccello\Core\Events\AfterSaveEvent;
use Uccello\Core\Models\Domain;
use Uccello\Core\Models\Module;
use App\Task;

class DetailController extends Controller
{
    /**
     * Update task "done" status
     *
     * @param Domain|null $domain
     * @param Module $module
     * @param Request $request
     * @return Illuminate\Http\Response
     */
    public function updateDone(?Domain $domain, Module $module, Request $request)
    {
        $this->preProcess($domain, $module, $request);

        $record = Task::find(request('id'));
        if (!is_null($record)) {

            event(new BeforeSaveEvent($domain, $module, $request, $record, 'edit'));

            $record->done = request('value');
            $record->save();

            event(new AfterSaveEvent($domain, $module, $request, $record, 'edit'));
        }

        return redirect()->back();
    }
}
