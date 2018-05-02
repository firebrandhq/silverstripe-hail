<div id="hail-fetch-wrapper" class="$Running">
    <div class="dropdown">
        <button
                class="progress-button dropdown-toggle $Disabled $Active $Global"
                data-style="fill"
                data-horizontal=""
                type="button"
                id="hail-fetch-button"
                data-toggle="dropdown"
                aria-haspopup="true"
                aria-expanded="false"
        >
            <span class="content">
                <% if $Active %>
                    Loading status...
                <% else %>
                    FETCH
                <% end_if %>
            </span>
            <span class="progress">
                <span class="progress-inner"></span>
            </span>
        </button>
        <div class="dropdown-menu hail-fetch-items" aria-labelledby="hail-fetch-button">
            <a class="dropdown-item" data-to-fetch="*">All</a>
            <a class="dropdown-item" data-to-fetch="Firebrand-Hail-Models-Article">Articles</a>
            <a class="dropdown-item" data-to-fetch="Firebrand-Hail-Models-Publication">Publications</a>
            <a class="dropdown-item" data-to-fetch="Firebrand-Hail-Models-PublicTag">Public tags</a>
            <a class="dropdown-item" data-to-fetch="Firebrand-Hail-Models-PrivateTag">Private tags</a>
        </div>
    </div>
</div>