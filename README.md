# ligaclash
PHP pages to calculate best way to distribute cwl bonus

This pages, uses Clash of Clans Developer API to get all war data from clash of clans war league, and sumarises total of points made by each player of a clan.

Ok,but... we already have a screen to do this in the game.

This calculation its a little bit smart. It will evaluate if you are attacking a village with a TH level lower or higher than your level. With this little change, stars summarization are more balanced end realistic.

If a Player didn't attack during a war (I called W.O.), it hill be put in the best players list.

So, this is the logic:

 - Count the exact number of stars of the attack: If a player attack a opponent village with the same TH level.
 - Decrease by 1 star,  the number of stars of the attack: If a player attack a opponent village with TH level lower then it self. 
      Ex.: If a th12 attack a th11 (or a th10) and get only 2 stars. In the end of war, the system will count only 1 star, to the final summary.  
 - Increase by 1 star,  the number of stars of the attack: If a player attack a opponent village with TH level lower then it self. 
      Ex.: If a th12 attack a th13 (or a th14) and get only 2 stars. In the end of war, the system will count only 3 star, to the final summary.
 - If a player don't attack: It will be put in the end of the list of players bonus candidates.

After each war, using the link Results, its possible see how players are performing during the current league.

I think that's all
      
