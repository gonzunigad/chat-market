<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import {Inertia} from "@inertiajs/inertia";

const publish = async (ev) => {
    let response = await Inertia.post(route('list-chat'), {
        event_id: ev.id
    });

    console.log(await response.json());
}

const take = async (ev) => {
    let response = await Inertia.post(route('take-chat'), {
        listing_id: ev.listing.id
    });

    console.log(await response.json());
}
const deleteListing = async (ev) => {
    let response = await Inertia.delete(route('delete-listing'), {
        data: {listing_id: ev.listing.id}
    });

    console.log(await response.json());

}

const upgrade = async (ev) => {
    let response = await Inertia.delete(route('upgrade-listing'), {
        data: {listing_id: ev.listing.id}
    });

    console.log(await response.json());

}
</script>

<template>
    <AppLayout title="Dashboard">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                ¡Hola {{ $page.props.user.name }}! Tienes
                <strong class="font-bold text-2xl" :class="{'text-red-600': $page.props.chatCoins < 0}">
                    {{ $page.props.chatCoins }}
                </strong> puntos de Karma
            </h2>
        </template>

        <div v-if="$page.props.flash.message" class="bg-green-600 text-white my-10 p-2 px-6 rounded shadow max-w-6xl mx-auto ">
            {{ $page.props.flash.message.message }}
        </div>

        <div class="py-12 grid grid-cols-3 gap-4 max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="px-6 border-r border-gray-300">
                <h3 v-if="$page.props.yourEvents.length > 0" class="text-lg font-bold pb-6">Tus chats</h3>
                <div class="grid grid-cols-1 gap-4">

                    <div class="group bg-white overflow-hidden shadow-xl sm:rounded-lg p-4"
                         v-for="event in $page.props.yourEvents">
                        <div class="flex">
                            <div>
                                <strong>{{ event.dayOfWeek }}</strong> <br>
                                {{ event.friendlyTime }} <br>
                            </div>

                            <div class="ml-auto" v-if="event.canBePublished">
                                <a @click.prevent="publish(event)" href=""
                                   class="group-hover:opacity-100 opacity-0 transition p-2 bg-blue-500 hover:bg-blue-800 rounded text-white text-center mt-2 block">
                                    Publicar
                                </a>
                            </div>
                        </div>


                        <div v-if="event.listing && event.listing.user.id === $page.props.user.id && event.listing.accepted_by === null"
                             class="text-lg  font-bold text-right">
                            Publicado
                        </div>

                        <div v-if="event.listing && event.listing.accepted_by !== null"
                             class="text-lg text-green-600 font-bold text-right">
                            <span v-if="event.listing.user.id === $page.props.user.id">
                                Aceptado por {{ event.listing.accepted_by_user.name }}
                            </span>
                            <span v-else class="text-gray-700">
                                Aceptaste este turno de {{ event.listing.user.name }}
                            </span>

                        </div>
                    </div>
                </div>
            </div>


            <div class="col-span-2">
                <h3 class="text-lg font-bold pb-6 ">Chats disponibles para cubrir</h3>
                <div class="grid grid-cols-3 gap-4">
                    <div class="group bg-white overflow-hidden shadow-xl sm:rounded-lg p-4"
                         :class="{'featured': event.listing.offered_coins === 2}"
                         v-for="event in $page.props.listings">

                        <span class="text-lg font-bold block pb-4" v-if="event.listing.offered_coins === 2">
                            Turno con doble Karma 🚀
                        </span>

                        <strong>{{ event.dayOfWeek }}</strong> <br>
                        {{ event.friendlyTime }} <br>
                        <span class="pt-2 text-sm block">Publicado por {{ event.listing.user.name }} </span>
                        <span class="text-xs"> ({{ event.listing.user.email }})</span>

                        <a @click.prevent="upgrade(event)" href=""
                           v-if="$page.props.user.id == event.listing.user_id && event.listing.offered_coins === 1"
                           class="p-2 bg-green-500 hover:bg-green-600 rounded text-white text-center mt-2 block">
                            Potenciar 🚀 (Karma 2x)
                        </a>
                        <a @click.prevent="deleteListing(event)" href=""
                           v-if="$page.props.user.id == event.listing.user_id"
                           class="p-2 bg-red-500 hover:bg-red-700 rounded text-white text-center mt-2 block">
                            Eliminar
                        </a>
                        <a href="" @click.prevent="take(event)" v-if="$page.props.user.id != event.listing.user_id"
                           class="group-hover:opacity-100 opacity-0 transition p-2 bg-blue-500 hover:bg-blue-800 rounded text-white text-center mt-2 block">
                            Tomar
                        </a>
                        <!--<div>-->
                        <!--    <ul>-->
                        <!--        <li v-for="attendee in event.attendees">-->
                        <!--            {{ attendee.username }}-->
                        <!--        </li>-->
                        <!--    </ul>-->
                        <!--</div>-->
                    </div>
                    <div v-if="$page.props.listings.length <= 0 ">
                        Aún no hay publicaciones :(
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
<style scoped>
.featured {
    background: url("https://thumbs.gfycat.com/ElementaryMeanBigmouthbass-size_restricted.gif");
    color: white;
}
</style>
