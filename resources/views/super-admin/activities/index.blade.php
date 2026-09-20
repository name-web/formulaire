<x-layouts.admin :title="'Activités'">

    <section class="intro">
        <div>
            <div class="intro-label">
                <span></span>
                Journal global
            </div>
            <h1>Activités</h1>
            <p>Les actions des commerciaux et l'évolution des prospects attribués.</p>
        </div>
    </section>

    <x-activity-feed
        :activities="$activities"
        :authors="$authors"
        index-route="super_admin.activities.index"
        context="super_admin"
    />

</x-layouts.admin>
