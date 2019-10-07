Pour ajouter un nouveau service :
- Ajouter une entrée dans la table "calendar_types" en modifiant le fichier de migration XXXX_create_calendar_types_table
- Créer un dossier {ServiceName} dans uccello/calendar/app/Http/Controllers


Dans app/Console/Kernel.php ajouter ceci pour qu'un classement automatique des évenements soit réalisé :
protected function schedule(Schedule $schedule)
{
    $users = User::all();
    $month_ago = date('Y-m-d', strtotime("-1 month"));
    $month_later = date('Y-m-d', strtotime("+1 month"));


    foreach($users as $user)
    {
        $schedule->command("events:classify $user->id $month_ago $month_later")->daily();  
    }

    //Add other crons here
}