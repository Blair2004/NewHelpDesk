<template>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <div class="text-center">
            <h1 class="text-4xl font-bold text-gray-900 mb-4">
                Welcome to Helpdesk
            </h1>
            <p class="text-xl text-gray-600 mb-8">
                Get help and support from our team
            </p>
            <router-link to="/login" class="btn btn-primary text-lg">
                Get Started
            </router-link>
        </div>

        <div class="mt-16 grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="card text-center">
                <ChatBubbleLeftRightIcon class="w-12 h-12 mx-auto text-blue-600 mb-4" />
                <h3 class="text-lg font-semibold mb-2">Support Tickets</h3>
                <p class="text-gray-600">Create and track support tickets</p>
            </div>
            <div class="card text-center">
                <GlobeAltIcon class="w-12 h-12 mx-auto text-blue-600 mb-4" />
                <h3 class="text-lg font-semibold mb-2">Multilingual</h3>
                <p class="text-gray-600">AI-powered translation support</p>
            </div>
            <div class="card text-center">
                <BoltIcon class="w-12 h-12 mx-auto text-blue-600 mb-4" />
                <h3 class="text-lg font-semibold mb-2">Fast Response</h3>
                <p class="text-gray-600">Quick replies from our team</p>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ChatBubbleLeftRightIcon, GlobeAltIcon, BoltIcon } from '@heroicons/vue/24/outline';
</script>
