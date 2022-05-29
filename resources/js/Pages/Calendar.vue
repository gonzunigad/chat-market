<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import {Inertia} from "@inertiajs/inertia";

const publish = async (event) => {
    let response = await Inertia.post(route('list-chat'), {
        event_id: event.id
    });

    console.log(await response.json());
}

const take = async (event) => {
    let response = await Inertia.post(route('take-chat'), {
        listing_id: event.listing.id
    });

    console.log(await response.json());
}

</script>

<template>
    <AppLayout title="Dashboard">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                ¡Hola {{ $page.props.user.name }}! Tienes <strong class="font-bold text-2xl" :class="{'text-red-600': $page.props.chatCoins < 0}">
                {{ $page.props.chatCoins }}</strong> ChatCoins™
                <span class="text-2xl" v-if="$page.props.chatCoins < 0">😢</span>
            </h2>
        </template>

        <div class="py-12 grid grid-cols-3 gap-4 max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="px-6">
                <h3 v-if="$page.props.yourEvents.length > 0" class="text-lg font-bold pb-6">Tus chats</h3>
                <div class="grid grid-cols-1 gap-4">
                    <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-4"
                         v-for="event in $page.props.yourEvents">
                        <strong>{{ event.dayOfWeek }}</strong> <br>
                        {{ event.friendlyTime }} <br>

                        <div v-if="event.canBePublished">
                            <a @click.prevent="publish(event)" href=""
                               class="p-2 bg-blue-500 hover:bg-blue-800 rounded text-white text-center mt-2 block">
                                Publicar turno
                            </a>
                        </div>
                    </div>
                </div>
            </div>


            <div class="col-span-2">
                <h3 class="text-lg font-bold pt-6 pb-6 ">Chats disponibles para cubrir</h3>
                <div class="grid grid-cols-3 gap-4">
                    <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-4"
                         v-for="event in $page.props.listings">
                        <strong>{{ event.dayOfWeek }}</strong> <br>
                        {{ event.friendlyTime }} <br>

                        <a href="" v-if="$page.props.user.id == event.listing.user_id"
                           class="p-2 bg-red-500 hover:bg-red-800 rounded text-white text-center mt-2 block">
                            Eliminar
                        </a>
                        <a @click.prevent="take(event)" v-if="$page.props.user.id != event.listing.user_id"
                           class="p-2 bg-blue-500 hover:bg-blue-800 rounded text-white text-center mt-2 block">
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
