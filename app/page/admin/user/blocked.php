<?php
$_title = "Blocked User";

$fields = [
    ' '            => '<input type="checkbox" class="select-box-all">',
    'id'           => 'ID',
    'first_name'   => 'First Name',
    'last_name'    => 'Last Name',
    'email'        => 'Email',
    'phone_number' => 'Phone Number',
    'role'         => 'Role',
    'created_at'   => 'Created At',
    '  '           => 'Action',
];

$role       = req('role', '');
$table_rows = req('table_rows', 0);
$search     = req('search');

$sort = req('sort');
key_exists($sort, $fields) || $sort = 'id';

$dir = req('dir');
in_array($dir, ['asc', 'desc']) || $dir = 'asc';

$page = req('page', 1);

global $_USER_DATA;

$table_rows_query = get_table_rows($table_rows);

$users = new SimplePager(
    "SELECT * FROM users 
        WHERE (role = ? OR ? = '') AND ((first_name LIKE ?) OR (last_name LIKE ?) OR (email LIKE ?) OR (phone_number LIKE ?))
        AND role != 'superadmin'
        AND is_blocked = 1
        AND is_deleted = 0
        AND id != ?
        ORDER BY $sort $dir",
    [$role, $role, "%$search%", "%$search%", "%$search%", "%$search%", $_USER_DATA->id],
    $table_rows_query,
    $page
);

$arr = $users->result;
?>

<h1><?= $_title ?></h1>

<ul>
    <li>
        <a href="/admin/user" class="success">Go Back</a>
    </li>
</ul>

<ul>
    <li><b>Batch Action</b></li>
    <li>
        <label for="batch_action_roles">Roles: </label>
        <?= html_select("batch_action_roles", $_ROLES, '', 'form="select-form"'); ?>
        <button type="button" data-message="Are you sure to apply the selected batch action?" form="select-form"
            data-action="/admin/user/update_role?forward=<?= getForwardUrl() ?>"
            class="select-btn select-submit-btn primary">Apply</button>
    </li>
    <li>
        <button type="button" data-message="Are you sure to unblock all the selected users?" form="select-form"
            data-action="/admin/user/unblock" class="select-btn select-submit-btn warning">Unblock</button>
    </li>
    <li>
        <button type="button" data-message="Are you sure to delete all the selected users?" form="select-form"
            data-action="/admin/user/delete" class="select-btn select-submit-btn danger">Delete</button>
    </li>
</ul>

<ul>
    <li>
        <label for="role_sort">Role:</label>
        <?= html_select('role_sort', $_ROLES) ?>
    </li>
    <li>
        <label for="table_rows">Rows: </label>
        <?= html_select("table_rows", TABLE_ROWS_LIST, $table_rows); ?>
    </li>
</ul>

<ul>
    <li>
        <?= $users->count ?> of <?= $users->item_count ?> record(s) |
        Page <?= $users->page ?> of <?= $users->page_count ?>
    </li>
</ul>

<form>
    <?= html_search("search", "data-search"); ?>
    <input type="hidden" name="role" value="<?= $role ?>">
    <input type="hidden" name="sort" value="<?= $sort ?>">
    <input type="hidden" name="dir" value="<?= $dir ?>">
    <input type="hidden" name="table_rows" value="<?= $table_rows ?>">
</form>

<form action="" method="post" id="select-form"></form>

<table class="table detail">
    <thead>
        <tr>
            <?= table_headers($fields, $sort, $dir, "role=$role&search=$search&page=$page&table_rows=$table_rows") ?>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($arr)) : ?>
            <tr>
                <td colspan="<?= count($fields) ?>">No record found</td>
            </tr>
        <?php endif; ?>
        <?php foreach ($arr as $u) : ?>
            <tr>
                <td><?= html_checkbox('id[]', $u->id, null, "form='select-form' class='select-box'"); ?></td>
                <td><?= $u->id ?></td>
                <td><?= html_print($u->first_name) ?></td>
                <td><?= html_print($u->last_name) ?></td>
                <td><?= html_print($u->email) ?></td>
                <td><?= html_print($u->phone_number) ?></td>
                <td><?= html_print($u->role) ?></td>
                <td><?= $u->created_at ?></td>
                <td>
                    <button data-get="/admin/user/view?id=<?= $u->id ?>&forward=<?= getForwardUrl() ?>"
                        class="primary">View</button>
                    <button data-get="/admin/user/update?id=<?= $u->id ?>&forward=<?= getForwardUrl() ?>"
                        class="success">Update</button>
                    <button data-post="/admin/user/unblock?id=<?= $u->id ?>"
                        data-confirm="Are you sure to unblock <?= html_print($u->email) ?>?"
                        class="warning">Unblock</button>
                    <button data-post="/admin/user/delete?id=<?= $u->id ?>"
                        data-confirm="Are you sure to delete <?= html_print($u->email) ?>?" class="danger">Delete</button>
                    <div class="popup">
                        <?= html_image($u->id, $u->image_url); ?>
                    </div>
                </td>
            </tr>
        <?php endforeach ?>
    </tbody>
</table>
<?= $users->html("role=$role&search=$search&sort=$sort&dir=$dir&table_rows=$table_rows"); ?>
