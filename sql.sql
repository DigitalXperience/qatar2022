select p.utilisateur_id, r.id as id_rencontre, p.dateheure as heure_prono, p.last_update, r.date_heure as heure_rencontre, p.pts_obtenus from pronostics p 
left join rencontres r on r.id = p.rencontre_id 

where p.utilisateur_id = 354