<x-layouts.admin :title="'Activités'">

    <section class="intro">
        <div>
            <div class="intro-label">
                <span></span>
                Journal central
            </div>
            <h1>Activités</h1>
            <p>Les actions menées sur vos prospects et clients, sans ouvrir chaque fiche.</p>
        </div>
    </section>

    <x-activity-feed
        :activities="$activities"
        :authors="$authors"
        index-route="admin.activities.index"
        context="admin"
    />

</x-layouts.admin>
