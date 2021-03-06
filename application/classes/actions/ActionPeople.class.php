<?php


class ActionPeople extends Action
{

    protected $sMenuHeadItemSelect = 'people';

    /**
     * Инициализация
     *
     */
    public function Init()
    {
        $this->SetDefaultEvent('');
    }

    /**
     * Регистрация евентов
     *
     */
    protected function RegisterEvent()
    {
         $this->AddEventPreg( '/^(page(\d))?$/i',  ['EventSearch' , 'people']);
    }


    public function EventSearch() {
        
        $iLimit = Config::Get('module.user.search.per_page');
        
        $iPage = $this->GetEventMatch(2);
        $iPage = $iPage?$iPage:1;
        
        $aFilter = [
            '#index-from'   => 'id',
            '#page'         => [$iPage, $iLimit],
            'activate'      => 1,
            'role'          => 'user'
        ];
        
        $aUsers = $this->User_GetUserItemsByFilter($aFilter);

            
                
        $aPaging = $this->Viewer_MakePaging($aUsers['count'], $iPage, $iLimit, 
                Config::Get('module.user.search.pagination.pages_count'), Router::GetPath('people'));

        $this->assign('aPaging', $aPaging);
        $this->assign('aUsers', $aUsers['collection']);
        $this->assign('count', $aUsers['count']);
        $this->SetTemplateAction('search');
    }

}