<?php

/**
 * Description of ActionProfile_EventSettings
 *
 * @author oleg
 */
class ActionProfile_EventProfile extends Event {
    
       
    /**
     * Главная страница
     *
     */
    public function EventIndex()
    {
        $this->GetItemsByFilter([
            'target_id'     => $this->oUserProfile->getId(),
            'type in'       => ['response', 'proposal']
        ], 'all');
                
        $this->sMenuHeadItemSelect = 'index';
        $this->SetTemplateAction('index');
    }
    
    
    public function EventResponses()
    {
        $this->GetItemsByFilter([
            'target_id'     => $this->oUserProfile->getId(),
            'type in'       => ['response']
        ], 'responses');
        
        $this->sMenuHeadItemSelect = 'responses';
        $this->SetTemplateAction('responses');
    }
    
    public function EventProposals() {
        $this->GetItemsByFilter([
            'target_id'     => $this->oUserProfile->getId(),
            'type in'       => ['proposal']
        ], 'proposals');
        
        $this->sMenuHeadItemSelect = 'proposals';
        $this->SetTemplateAction('proposals');
    }
    
    
    public function EventMyResponses()
    {
        $oUserCurrent = $this->User_GetUserCurrent();
        if($oUserCurrent and $oUserCurrent->getId() != $this->oUserProfile->getId()){
            return $this->EventNotFound();
        }
        
        $this->GetItemsByFilter([
            'user_id'       => $oUserCurrent->getId(),
            'type in'       => ['response']
        ], 'responses');
        
        $this->sMenuHeadItemSelect = 'my-responses';
        $this->SetTemplateAction('my-responses');
    }
    
    public function EventMyProposals() {
        $oUserCurrent = $this->User_GetUserCurrent();
        if($oUserCurrent and $oUserCurrent->getId() != $this->oUserProfile->getId()){
            return $this->EventNotFound();
        }
        
        $this->GetItemsByFilter([
            'user_id'       => $oUserCurrent->getId(),
            'type in'       => ['proposal']
        ], 'proposals');
        
        $this->sMenuHeadItemSelect = 'my-proposals';
        $this->SetTemplateAction('my-proposals');
    }
    
    protected function GetItemsByFilter($aFilter, $sPageName) {
        $iLimit = Config::Get('module.talk.page_count');
        
        $iPage = $this->GetParamEventMatch(1,2);
        $iPage = $iPage?$iPage:1;
        
        $aFilter = array_merge($aFilter, [
            '#with'         => ['user'],
            '#index-from'   => 'id',
            '#order'        => ['date_create' => 'desc'],
            '#page'         => [$iPage, $iLimit],
            'state'         => 'publish'
        ]);
        
        $aMessages = $this->Talk_GetMessageItemsByFilter($aFilter);

        /*
         * Прикрепляем Media в соответствии с типом
         */
        $aSortTypeResuls = [];
        foreach ($aMessages['collection'] as $oResult) {
            if(!isset($aSortTypeResuls[$oResult->getType()])){
                $aSortTypeResuls[$oResult->getType()] = [];
            }
            $aSortTypeResuls[$oResult->getType()][]  = $oResult;
        } 
        
        foreach ($aSortTypeResuls as $type => $aResults) {
            $this->Media_AttachMediasForTargetItems($aResults, $type);
        }      
                
        $aPaging = $this->Viewer_MakePaging($aMessages['count'], $iPage, $iLimit, 
                Config::Get('module.talk.pagination.pages.count'), Router::GetPath($this->sCurrentEvent.'/'.$sPageName));
        
        $this->Viewer_Assign('aPaging', $aPaging);
        $this->Viewer_Assign('results', $aMessages['collection']);
        $this->Viewer_Assign('count', $aMessages['count']);
    }
    
}