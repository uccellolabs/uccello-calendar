<?php

namespace Uccello\Calendar\Http\Controllers\Core;

use Illuminate\Http\Request;
use Uccello\Core\Models\Domain;
use Uccello\Core\Models\Module;
use Uccello\Core\Models\Widget;
use Uccello\Core\Models\Relatedlist;
use Uccello\Core\Http\Controllers\Core\DetailController;


class DetailControllerSpecific extends DetailController
{
    /**
     * {@inheritdoc}
     */
    public function processSpecific(?Domain $domain, Module $module, $id, Request $request)
    {
        // Pre-process
        $this->preProcess($domain, $module, $request);

        // Get record id
        $recordId = $id;

        // Selected tab
        $selectedTabId = (int)$request->input('tab');

        // Selected related list
        $selectedRelatedlistId = (int)$request->input('relatedlist');

        // Check if the selected related list is visible
        if ($selectedRelatedlistId) {
            $relatedlist = Relatedlist::find($selectedRelatedlistId);
            if (empty($relatedlist) || !$relatedlist->isVisibleAsTab) {
                $selectedRelatedlistId = false;
            }
        }

        // Widgets
        $availableWidgets = Widget::where('type', 'summary')->get(); // TODO: Don't display widgets already added
        $widgets = $module->widgets()->withPivot('sequence')->orderBy('pivot_sequence')->get(); // TODO: Get wigets with priority (1. User 2. Domain 3. Default)

        return $this->autoView([
            'record' => $this->getRecord($recordId),
            'selectedTabId' => $selectedTabId,
            'selectedRelatedlistId' => $selectedRelatedlistId,
            'widgets' => $widgets,
            'availableWidgets' => $availableWidgets,
        ]);
    }
}
