<nav id="admin-bar">

<?php if($user = current_user()) {

    if ($user->role == "super")
    {
        $links = array(
            array(
                'label' => __('Welcome, %s', $user->name),
                'uri' => admin_url('/users/edit/'.$user->id)
            ),
            array(
                'label' => __('Omeka Admin'),
                'uri' => admin_url('/')
            ),
            array(
                'label' => __('Log Out'),
                'uri' => url('/users/logout')
            )
        );
    } else {
        $links = array(
            array(
                'label' => __('Log Out'),
                'uri' => url('/users/logout')
            )
        );
    }
    

} else {
    $links = array();
}


if ($user->role != "super")
{
    echo "<ul class='navigation'><li>".__('Welcome, %s', $user->name)."&nbsp;</li></ul>";
}

echo nav($links, 'public_navigation_admin_bar');

?>
</nav>
