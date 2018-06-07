# SugarAndFilterForTags
Added a new type of filter ($and_in) with an AND condition between all tags passed to the $and_in condition

## Notes
The caviet of this solution is that when passing few tags to the request, the system might reach the database join limit imposed by the underlying technology. This solution works best with a handful of tags selected at most, as two additional joins are enforced for every tag passed
For this reason, the API end of the solution imposes a hard limit of 5 tags (10 additional SQL joins)

## Environment
Sugar Enterprise 8.0.0 on MySQL

## Installation
* Clone the repository.
* Run: `composer update` to retrieve the sugar-module-packager dependency.
* Modify the list of modules to enable the functionality on `configuration/templates.php`
* Generate the installable .zip Sugar module with: `./vendor/bin/package <version number>`.

## API Call Example

`{{url}}/rest/v10/Contacts?fields=first_name,last_name,id,date_entered&filter[0][tag][$and_in][]=basketball&filter[0][tag][$and_in][]=table tennis`

The above example will only match all Contacts that are related to both the basketball and the table tennis tags

## Result Output

```
{
    "next_offset": -1,
    "records": [
        {
            "id": "604b7e1e-68a1-11e8-842c-3583ef1b1d77",
            "date_entered": "2018-06-05T19:18:14+10:00",
            "date_modified": "2018-06-05T19:18:14+10:00",
            "first_name": "Enrico",
            "last_name": "Simonetti",
            "locked_fields": [],
            "_acl": {
                "fields": {}
            },
            "_module": "Contacts"
        }
    ]
}
```
 

## MySQL Query

`SELECT contacts.first_name, contacts.last_name, contacts.id, contacts.date_entered, contacts.date_modified, contacts.assigned_user_id, contacts.created_by FROM contacts INNER JOIN (SELECT tst.team_set_id FROM team_sets_teams tst INNER JOIN team_memberships team_membershipscontacts ON (team_membershipscontacts.team_id = tst.team_id) AND (team_membershipscontacts.user_id = ?) AND (team_membershipscontacts.deleted = 0) GROUP BY tst.team_set_id) contacts_tf ON contacts_tf.team_set_id = contacts.team_set_id INNER JOIN tag_bean_rel tag_bean_rel_1 ON (contacts.id = tag_bean_rel_1.bean_id) AND (tag_bean_rel_1.bean_module = ?) AND (tag_bean_rel_1.deleted = ?) INNER JOIN tags tags_1 ON (tag_bean_rel_1.tag_id = tags_1.id) AND (tags_1.deleted = ?) AND (tags_1.name_lower = ?) INNER JOIN tag_bean_rel tag_bean_rel_2 ON (contacts.id = tag_bean_rel_2.bean_id) AND (tag_bean_rel_2.bean_module = ?) AND (tag_bean_rel_2.deleted = ?) INNER JOIN tags tags_2 ON (tag_bean_rel_2.tag_id = tags_2.id) AND (tags_2.deleted = ?) AND (tags_2.name_lower = ?) WHERE contacts.deleted = ? LIMIT 21 OFFSET 0`

```
params: Array
(
    [1] => c0ddb56a-6493-11e8-9f7b-085cf8742e1e
    [2] => Contacts
    [3] => 0
    [4] => 0
    [5] => basketball
    [6] => Contacts
    [7] => 0
    [8] => 0
    [9] => table tennis
    [10] => 0
)
```
