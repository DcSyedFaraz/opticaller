<template>

    <Head title="Addresses" />
    <AuthenticatedLayout>
        <div class="user-page grid grid-cols-1 lg:grid-cols-4 gap-4 p-4"
            v-if="localAddress && localAddress.company_name">
            <!-- Main Panel -->
            <div class="col-span-1 lg:col-span-3 border rounded-xl shadow-xl">
                <div class="pb-2 p-4">
                    <div
                        class="inline-flex items-center rounded-md bg-black px-2 py-1 text-2xl xl:text-3xl font-medium text-white ring-1 ring-inset ring-black mb-1">
                        Sub Project Title: {{ localAddress.subproject?.title }}
                    </div>
                    <div class="flex flex-wrap justify-between items-center">
                        <div
                            class="inline-flex items-center rounded-md bg-primary px-2 py-1 text-lg md:text-xl font-medium text-white ring-1 ring-inset ring-primary mb-1">
                            Project Title: {{ localAddress.subproject?.projects?.title }}
                        </div>
                        <div class="inline-flex items-center rounded-md mr-2 mb-1 w-full md:w-auto">
                            <InputText id="contact_id" v-model="localAddress.contact_id" placeholder="Contact ID"
                                class="w-full !border-black" :disabled="isFieldLocked('contact_id')" />
                        </div>
                    </div>
                </div>
                <div class="border m-4 lg:m-7 rounded-lg shadow shadow-secondary">
                    <div class="border-b-2 p-3">
                        <span class="mx-4 font-extrabold text-lg">Details</span>
                    </div>

                    <div class="grid">
                        <Card class="shadow-md">
                            <template #content>
                                <div class="grid grid-cols-1 mx-3 gap-4">
                                    <div class="field">
                                        <label for="company_name" class="font-extrabold text-lg">
                                            Company Name: <span class="text-red-600">*</span>
                                        </label>
                                        <InputText id="company_name" v-model="localAddress.company_name"
                                            class="w-full !border-secondary"
                                            :disabled="isFieldLocked('company_name')" />
                                    </div>

                                    <div class="grid grid-cols-1 lg:grid-cols-5 gap-4">
                                        <div class="field col-span-1 md:col-span-1 lg:col-span-1">
                                            <label for="salutation" class="font-extrabold text-lg">
                                                Salutation: <span class="text-red-600">*</span>
                                            </label>
                                            <InputText id="salutation" v-model="localAddress.salutation"
                                                class="w-full !border-secondary"
                                                :disabled="isFieldLocked('salutation')" />
                                        </div>

                                        <div class="field col-span-1 md:col-span-1 lg:col-span-2">
                                            <label for="first_name" class="font-extrabold text-lg">
                                                First Name: <span class="text-red-600">*</span>
                                            </label>
                                            <InputText id="first_name" v-model="localAddress.first_name"
                                                class="w-full !border-secondary"
                                                :disabled="isFieldLocked('first_name')" />
                                        </div>
                                        <div class="field md:col-span-2">
                                            <label for="last_name" class="font-extrabold text-lg">
                                                Last Name: <span class="text-red-600">*</span>
                                            </label>
                                            <InputText id="last_name" v-model="localAddress.last_name"
                                                class="w-full !border-secondary"
                                                :disabled="isFieldLocked('last_name')" />
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-1 lg:grid-cols-5 gap-4">
                                        <div class="field md:col-span-2">
                                            <label class="font-extrabold text-lg" for="street_address">
                                                Street and House Number:
                                                <span class="text-red-600">*</span>
                                            </label>
                                            <InputText id="street_address" v-model="localAddress.street_address"
                                                class="w-full !border-secondary"
                                                :disabled="isFieldLocked('street_address')" />
                                        </div>
                                        <div class="field">
                                            <label class="font-extrabold text-lg" for="postal_code">
                                                Postal Code: <span class="text-red-600">*</span>
                                            </label>
                                            <InputText id="postal_code" v-model="localAddress.postal_code"
                                                class="w-full !border-secondary"
                                                :disabled="isFieldLocked('postal_code')" />
                                        </div>
                                        <div class="field col-span-1 md:col-span-1 lg:col-span-2">
                                            <label class="font-extrabold text-lg" for="city">
                                                City: <span class="text-red-600">*</span>
                                            </label>
                                            <InputText id="city" v-model="localAddress.city"
                                                class="w-full !border-secondary" :disabled="isFieldLocked('city')" />
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div class="field">
                                            <label class="font-extrabold text-lg" for="country">
                                                Country: <span class="text-red-600">*</span>
                                            </label>
                                            <InputText id="country" v-model="localAddress.country"
                                                placeholder="country name" class="w-full !border-secondary"
                                                :disabled="isFieldLocked('country')" />
                                        </div>
                                        <div class="field">
                                            <label class="font-extrabold text-lg" for="website">
                                                Website: <span class="text-red-600">*</span>
                                            </label>
                                            <InputText id="website" v-model="localAddress.website"
                                                class="w-full !border-secondary" :disabled="isFieldLocked('website')" />
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div class="field">
                                            <label class="font-extrabold text-lg" for="phone_number">
                                                Phone Number: <span class="text-red-600">*</span>
                                            </label>
                                            <InputText id="phone_number" v-model="localAddress.phone_number"
                                                class="w-full !border-secondary"
                                                :disabled="isFieldLocked('phone_number')" />
                                        </div>
                                        <div class="field">
                                            <label for="email_address_system" class="font-extrabold text-lg">
                                                Email Address: <span class="text-red-600">*</span>
                                            </label>
                                            <InputText id="email_address_system"
                                                v-model="localAddress.email_address_system"
                                                class="w-full !border-secondary"
                                                :disabled="isFieldLocked('email_address_system')" />
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </Card>
                    </div>
                </div>
                <!-- Notes and Buttons remain unchanged as they have flexible widths -->
                <div class="border m-4 lg:m-7 rounded-lg shadow shadow-blue-200 pb-4">
                    <div class="border-b-2 p-3">
                        <span class="mx-4 font-extrabold text-lg">Notes</span>
                    </div>
                    <div class="grid">
                        <Card class="shadow-md">
                            <template #content>
                                <div class="grid">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div class="field">
                                            <label class="font-extrabold text-lg" for="feedback">
                                                Feedback: <span class="text-red-600">*</span>
                                            </label>
                                            <Select id="feedback" v-model="localAddress.feedback"
                                                class="w-full !border-secondary" :options="feedbackOptions"
                                                optionValue="value" optionLabel="label" />
                                        </div>
                                        <div class="field">
                                            <label class="font-extrabold text-lg" for="interest_notes">
                                                Interest Notes: <span class="text-red-600">*</span>
                                            </label>
                                            <Textarea id="interest_notes" v-model="logdata.interest_notes" rows="5"
                                                class="w-full !border-secondary" />
                                        </div>
                                    </div>
                                    <div class="field">
                                        <label class="font-extrabold text-lg" for="personal_notes">
                                            Personal Notes: <span class="text-red-600">*</span>
                                        </label>
                                        <Textarea id="personal_notes" v-model="logdata.personal_notes" rows="2"
                                            class="w-full !border-secondary" />
                                    </div>
                                </div>
                            </template>
                        </Card>
                    </div>
                </div>
                <div class="flex flex-col lg:flex-row justify-center my-4">
                    <button @click="notreached = true; submitFeedback()"
                        class="bg-[#A7704A] text-white font-bold py-2 px-6 rounded hover:bg-primary flex align-middle mx-2 mb-2 lg:mb-0">
                        <!-- SVG and Text for Button remain unchanged -->
                        <svg width="25" height="25" viewBox="0 0 25 25" class="my-1" fill="none"
                            xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M22.4383 24.261C19.9103 24.261 17.3183 23.628 14.6623 22.362C12.0063 21.096 9.53434 19.3185 7.24634 17.0295C4.97634 14.7415 3.20833 12.274 1.94233 9.62705C0.676335 6.98005 0.043335 4.39305 0.043335 1.86605C0.043335 1.41605 0.193335 1.03605 0.493335 0.726047C0.793335 0.416047 1.16834 0.261047 1.61834 0.261047H5.32783C5.73583 0.261047 6.09183 0.389547 6.39583 0.646547C6.69983 0.903547 6.90533 1.23305 7.01233 1.63505L7.75634 5.21105C7.82634 5.63105 7.81383 5.99755 7.71883 6.31055C7.62383 6.62355 7.45734 6.88005 7.21934 7.08005L3.92834 10.149C4.54434 11.266 5.22484 12.304 5.96984 13.263C6.71484 14.222 7.50984 15.1305 8.35484 15.9885C9.22484 16.8585 10.1618 17.6685 11.1658 18.4185C12.1698 19.1675 13.2718 19.8745 14.4718 20.5395L17.6803 17.274C17.9243 17.011 18.1988 16.8375 18.5038 16.7535C18.8078 16.6705 19.1448 16.654 19.5148 16.704L22.6693 17.349C23.0773 17.449 23.4083 17.6545 23.6623 17.9655C23.9163 18.2765 24.0433 18.6325 24.0433 19.0335V22.686C24.0433 23.136 23.8883 23.511 23.5783 23.811C23.2683 24.111 22.8883 24.261 22.4383 24.261Z"
                                fill="white" />
                            <path d="M15.0433 2.26105L20.3483 7.56605M20.3483 2.26105L15.0433 7.56605" stroke="white"
                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />

                        </svg>
                        <span class="mx-2 my-1">Not Reached</span>
                    </button>
                    <button @click="showFollowModal = true" :disabled="this.localAddress.feedback != 'Follow-up'"
                        class="bg-[#383838] text-white font-bold py-2 px-6 rounded hover:bg-[#161616] disabled:bg-[#464545] disabled:cursor-not-allowed flex align-middle mx-2 mb-2 lg:mb-0">
                        <!-- SVG and Text for Button remain unchanged -->
                        <svg width="33" height="33" viewBox="0 0 33 33" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M1.60718 1.66168V18.2422H25.4797V1.66168" stroke="white" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round" />
                            <path d="M17.5222 22.8817L13.5434 26.8604L9.5647 22.8817" stroke="white" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round" />
                            <path d="M13.5435 18.2398V26.8604" stroke="white" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" />
                            <path d="M16.1885 5.62616L19.5116 8.95604L16.1885 12.3315" stroke="white" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round" />
                            <path d="M7.57593 8.95673H19.5116" stroke="white" stroke-width="2" stroke-linecap="round" />

                        </svg>
                        <span class="mx-2 my-1">Follow-Ups</span>
                    </button>
                    <button @click="submitFeedback" :disabled="this.localAddress.feedback == 'Follow-up'"
                        class="bg-secondary text-white font-bold py-2 px-6 rounded hover:bg-secondary/75 flex align-middle mx-2 disabled:bg-secondary/75 disabled:cursor-not-allowed">
                        <span class="mx-2 my-1">
                            <i class="pi pi-save"></i> Save Edits
                        </span>
                    </button>
                </div>
                <!-- Call History remains unchanged with the addition of responsive adjustments -->
                <div class="call-history lg:col-span-1 hidden">
                    <Card class="shadow-md">
                        <template #title>
                            <h2 class="text-lg font-bold">Call History</h2>
                        </template>
                        <template #content>
                            <DataTable :value="callHistory" class="w-full !border-secondary" scrollable
                                scrollHeight="250px">
                                <Column field="created_at" header="Date">
                                    <template #body="slotProps">
                                        {{ formatDate(slotProps.data) }}
                                    </template>
                                </Column>
                                <Column header="Call Attempts">
                                    <template #body="slotProps">
                                        {{ slotProps.data.notes?.call_attempts ?? 'N/A' }}
                                    </template>
                                </Column>
                                <Column header="Personal Notes">
                                    <template #body="slotProps">
                                        <Button @click="showNotes(slotProps.data.notes?.personal_notes)"
                                            v-if="slotProps.data.notes?.personal_notes">
                                            View Notes
                                        </Button>
                                        <span v-else>N/A</span>
                                    </template>
                                </Column>
                                <template #empty>No call logs available.</template>
                            </DataTable>
                        </template>
                    </Card>
                </div>
            </div>
            <!-- Side Panel -->
            <div class="col-span-1 lg:w-2/3">
                <!-- <Button
            label="Callback"
            icon="pi pi-phone"
            class="w-full my-2"
            size="large"
            @click="showCallbackForm = true"
          /> -->
                <Link :href="route('callback')" class="p-button p-component p-button-lg w-full my-2">
                <i class="pi pi-phone !text-xl"></i> Callback
                </Link>
                <Button @click="togglePause" :label="isPaused ? 'End Break' : 'Take Break'"
                    :class="isPaused ? '!bg-red-500 !border-red-500' : '!bg-secondary !border-secondary'"
                    class="w-full my-2" size="large">
                    <template #icon>
                        <svg v-if="!isPaused" width="26" height="32" viewBox="0 0 26 37" fill="none"
                            xmlns="http://www.w3.org/2000/svg">
                            <path d="M1.66064 12.0177H24.4261" stroke="white" stroke-width="3" stroke-linecap="round" />
                            <path
                                d="M5.03737 13.7601C5.0214 13.5373 5.05149 13.3137 5.12577 13.103C5.20005 12.8924 5.31693 12.6993 5.46911 12.5358C5.6213 12.3724 5.80553 12.242 6.01032 12.1529C6.2151 12.0637 6.43606 12.0177 6.65941 12.0177H19.4276C19.6509 12.0177 19.8717 12.0637 20.0765 12.1528C20.2812 12.2419 20.4654 12.3722 20.6176 12.5356C20.7697 12.6989 20.8866 12.8919 20.961 13.1025C21.0353 13.313 21.0655 13.5366 21.0496 13.7593L19.656 33.2725C19.6268 33.6828 19.4433 34.0667 19.1423 34.3471C18.8413 34.6274 18.4453 34.7832 18.034 34.7832H8.05298C7.64169 34.7832 7.24567 34.6274 6.94469 34.3471C6.64372 34.0667 6.46014 33.6828 6.43094 33.2725L5.03737 13.7601Z"
                                stroke="white" stroke-width="3" />
                            <path
                                d="M13.0435 7.13939V3.88718C13.0435 3.45591 13.2148 3.04231 13.5197 2.73735C13.8247 2.4324 14.2383 2.26108 14.6696 2.26108H17.1087"
                                stroke="white" stroke-width="3" stroke-linecap="round" />
                            <path
                                d="M5.49987 8.49801C5.56316 8.11847 5.75901 7.77366 6.05257 7.5249C6.34614 7.27614 6.71841 7.13955 7.10321 7.1394H18.9835C19.3685 7.13936 19.7409 7.27586 20.0347 7.52464C20.3284 7.77342 20.5244 8.11833 20.5877 8.49801L21.1739 12.0177H4.91284L5.49987 8.49801Z"
                                stroke="white" stroke-width="3" />

                        </svg>
                        <i v-else class="pi pi-play !text-2xl"></i>
                    </template>
                </Button>

                <!-- Timers and Call History UI Adjustments for responsiveness -->
                <div class="grid grid-cols-1 sm:grid-cols-3 border my-3 rounded-md p-4 shadow shadow-secondary">
                    <div class="col-span-2">
                        <p class="text-[#424E79] font-sans text-sm">Login Time</p>
                        <h1 class="text-[#424E79] font-sans font-extrabold text-xl">
                            {{ loginTime }}
                        </h1>
                    </div>
                    <div class="col-span-1 hidden xl:flex justify-end">
                        <span class="pi pi-clock !text-[3rem] text-primary ml-2" data-pc-section="icon"></span>
                    </div>
                </div>

                <!-- Additional components with similar adjustments -->
                <div class="grid grid-cols-1 sm:grid-cols-3 border my-3 rounded-md p-4 shadow shadow-secondary">
                    <div class="col-span-2">
                        <p class="text-[#424E79] font-sans text-[0.85rem]">Count down Time</p>
                        <h1 class="text-[#424E79] font-sans font-extrabold text-2xl">
                            {{ formattedReverseCountdown }}
                        </h1>
                    </div>
                    <div class="col-span-1 hidden xl:flex justify-end">
                        <span class="pi pi-clock !text-[3rem] ml-2" data-pc-section="icon"></span>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 border my-3 rounded-md p-4 shadow shadow-secondary">
                    <div class="col-span-2">
                        <p class="text-[#424E79] font-sans text-sm">Time on Page</p>
                        <h1 class="text-[#424E79] font-sans font-extrabold text-2xl">
                            {{ formattedCountdown }}
                        </h1>
                    </div>
                    <div class="col-span-1 hidden xl:flex justify-end">
                        <span class="pi pi-clock !text-[3rem] text-secondary ml-2" data-pc-section="icon"></span>
                    </div>
                </div>

                <!-- Responsive adjustments for call history and modal dialogs -->
                <div class="bg-white border border-gray-300 rounded-lg shadow-xl">
                    <div class="flex justify-between items-center pb-2 border-b-2 p-4">
                        <h3 class="text-lg font-semibold text-gray-800">Call History</h3>
                        <button class="text-gray-500 hover:text-gray-700 focus:outline-none" @click="showNotes()">
                            <i class="pi pi-expand !text-xl text-secondary"></i>
                        </button>
                    </div>
                    <div class="my-3" v-for="(item, index) in callHistory.slice(0, 3)" :key="index"
                        v-if="callHistory.length > 0">
                        <div class="mb-2 p-4">
                            <span
                                class="inline-flex items-center rounded-md bg-secondary px-2 py-1 text-[10px] font-medium text-white ring-1 ring-inset ring-secondary mb-1">
                                Last call on: {{ formatnewDate(item.created_at) }}
                            </span>
                            <span
                                class="inline-flex mx-2 items-center rounded-md bg-primary px-2 py-1 text-[10px] font-medium text-white ring-1 ring-inset ring-primary">
                                Country: {{ localAddress.country }}
                            </span>
                            <span
                                class="inline-flex items-center rounded-md bg-[#3E3E3E] my-1 px-2 py-1 text-[10px] font-medium text-white ring-1 ring-inset ring-[#3E3E3E]">
                                Agent: {{ item.users?.name }}
                            </span>
                        </div>
                        <div class="border-b border-gray-300 pt-2 p-4">
                            <div class="mb-4">
                                <div class="flex items-center mb-1">
                                    <span class="h-2 w-2 bg-primary rounded-full mr-2"></span>
                                    <span class="text-md font-[1000] text-green-600">Personal Notes:</span>
                                </div>
                                <p
                                    class="text-sm text-gray-700 font-bold overflow-hidden text-ellipsis whitespace-nowrap max-w-[200px]">
                                    {{ item.notes?.personal_notes?.slice(0, 50) }}
                                    {{ item.notes?.personal_notes?.length > 50 ? '...' : '' }}
                                </p>
                            </div>
                            <div>
                                <div class="flex items-center mb-1">
                                    <span class="text-md font-[1000] text-red-500">Interest Notes:</span>
                                </div>
                                <p
                                    class="text-sm text-gray-700 font-bold overflow-hidden text-ellipsis whitespace-nowrap max-w-[200px]">
                                    {{ item.notes?.interest_notes?.slice(0, 50) }}
                                    {{ item.notes?.interest_notes?.length > 50 ? '...' : '' }}
                                </p>
                            </div>
                            <span
                                class="inline-flex items-center rounded-md bg-[#A6A2A0] my-1 px-2 py-1 text-[10px] font-medium text-white ring-1 ring-inset ring-[#A6A2A0]">
                                Call Duration:
                                <span class="font-bold">
                                    {{
                item.total_duration < 60 ? item.total_duration + ' Seconds' :
                    Math.floor(item.total_duration / 60) + ' Minutes ' + (item.total_duration % 60)
                    + ' Seconds' }} </span>
                                </span>
                        </div>
                    </div>
                    <div v-else>
                        <div class="flex justify-center items-center h-[12rem]">
                            <div class="text-lg text-gray-500">No history</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Dialog components remain unchanged for responsiveness -->
            <Dialog header="Follow Up" v-model:visible="showFollowModal" modal class="rounded-lg shadow-lg"
                :style="{ width: '90%', maxWidth: '36rem' }">
                <div class="p-6 space-y-4">
                    <div class="space-y-2">
                        <div class="field">
                            <label for="follow_up_date">Follow-up Date:</label>
                            <DatePicker id="follow_up_date" showTime hourFormat="12" placeholder="Follow up datetime"
                                fluid v-model="localAddress.follow_up_date" class="w-full !border-secondary" />
                        </div>
                    </div>
                    <div class="flex justify-end">
                        <Button class="font-bold py-2 px-4" @click="submitFeedback">Schedule Follow-up</Button>
                    </div>
                </div>
            </Dialog>

            <Dialog header="Call History" v-model:visible="showNotesModal" :maximizable="true" modal
                :style="{ width: '100vw', height: '100vh' }" :breakpoints="{ '960px': '75vw', '640px': '100vw' }"
                :draggable="false" class="rounded-lg shadow-lg">
                <div class="my-3" v-for="(item, index) in callHistory" :key="index" v-if="callHistory.length > 0">
                    <div class="ml-2 mb-2">
                        <span
                            class="inline-flex items-center rounded-md bg-secondary mx-2 px-2 py-1 text-[12px] font-large text-white ring-1 ring-inset ring-secondary mb-1">
                            Last call on: {{ formatnewDate(item.created_at) }}
                        </span>
                        <span
                            class="inline-flex items-center rounded-md bg-primary mx-2 px-2 py-1 text-[12px] font-large text-white ring-1 ring-inset ring-primary">
                            Country: {{ localAddress.country }}
                        </span>
                        <span
                            class="inline-flex items-center rounded-md bg-[#3E3E3E] mx-2 my-1 px-2 py-1 text-[12px] font-large text-white ring-1 ring-inset ring-[#3E3E3E]">
                            Agent: {{ item.users?.name }}
                        </span>
                    </div>
                    <div class="border-b border-gray-300 pt-2 p-4">
                        <div class="mb-4">
                            <div class="flex items-center mb-1">
                                <span class="h-2 w-2 bg-primary rounded-full mr-2"></span>
                                <span class="text-md font-[1000] text-green-600">Personal Notes:</span>
                            </div>
                            <p class="text-sm text-gray-700 font-bold">{{ item.notes?.personal_notes }}</p>
                        </div>
                        <div>
                            <div class="flex items-center mb-1">
                                <span class="text-md font-[1000] text-red-500">Interest Notes:</span>
                            </div>
                            <p class="text-sm font-bold">{{ item.notes?.interest_notes }}</p>
                        </div>
                        <span
                            class="inline-flex items-center rounded-md bg-[#A6A2A0] my-1 px-2 py-1 text-[10px] font-medium text-white ring-1 ring-inset ring-[#A6A2A0]">
                            Call Duration:
                            <span class="font-bold">
                                {{
                item.total_duration < 60 ? item.total_duration + ' Seconds' :
                    Math.floor(item.total_duration / 60) + ' Minutes ' + (item.total_duration % 60)
                    + ' Seconds' }} </span>
                            </span>
                    </div>
                </div>
            </Dialog>

            <Dialog header="Callback Request" v-model:visible="showCallbackForm" modal class="rounded-lg shadow-lg"
                :style="{ width: '90%', maxWidth: '36rem' }">
                <div class="p-6 space-y-4">
                    <div class="space-y-2">
                        <div class="field">
                            <label for="project">Project:</label>
                            <Select id="project" v-model="callbackForm.project" :options="projectOptions"
                                optionValue="label" optionLabel="label" class="w-full" />
                        </div>
                        <div class="field">
                            <label for="salutation">Salutation:</label>
                            <InputText id="salutation" v-model="callbackForm.salutation" class="w-full" />
                        </div>
                        <div class="field">
                            <label for="first_name">First Name:</label>
                            <InputText id="first_name" v-model="callbackForm.firstName" class="w-full" />
                        </div>
                        <div class="field">
                            <label for="last_name">Last Name:</label>
                            <InputText id="last_name" v-model="callbackForm.lastName" class="w-full" />
                        </div>
                        <div class="field">
                            <label for="phoneNumber">Phone Number:</label>
                            <InputText id="phoneNumber" v-model="callbackForm.phoneNumber" class="w-full" />
                        </div>
                        <div class="field">
                            <label for="personal_notes">Notes:</label>
                            <Textarea id="personal_notes" v-model="callbackForm.notes" rows="5" class="w-full" />
                        </div>
                    </div>
                    <div class="flex justify-end">
                        <Button label="Submit" icon="pi pi-check" @click="submitCallbackRequest"
                            class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded-md transition duration-300 ease-in-out" />
                    </div>
                </div>
            </Dialog>
        </div>

        <div v-else
            class="flex items-center justify-center my-auto p-6 text-center bg-yellow-100 border border-yellow-400 text-yellow-700 rounded-md mx-3">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M13 16h-1v-4h-1m-1-4h.01M12 14h.01M9 9h3.03M15 11V9a3 3 0 00-6 0v2m6 0H9m0 0h3.03M13 16h-1v-4h-1m-1-4h.01M12 14h.01M9 9h3.03M15 11V9a3 3 0 00-6 0v2m6 0H9" />
            </svg>
            <p class="font-semibold">
                No address available at the moment. Please check back later or contact support for assistance.
            </p>
        </div>
        <div v-if="isLoading" class="loading-overlay">
            <ProgressSpinner style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);" />
        </div>
    </AuthenticatedLayout>
</template>



<script>
import moment from "moment";


export default {
    props: {
        address: Object,
        lockfields: Array,
    },
    data() {
        return {
            localAddress: { ...this.address },
            locallockfields: this.lockfields ? [...this.lockfields] : [],
            callHistory: [...(this.address?.cal_logs ?? [])],
            logdata: {},
            errors: {},
            form: {},
            countdown: 0,
            ReverseCountdown: 180,
            pauseTime: 0,
            projectTitle: '',
            feedback: '',
            notes: '',
            isPaused: false,
            timer: null,
            reversetimer: null,
            showCallbackForm: false,
            callbackForm: {
                project: null,
                salutation: '',
                firstName: '',
                lastName: '',
                phoneNumber: '',
                notes: '',
            },
            startTime: 0,
            loginTime: new Date(this.$page.props.auth.logintime).toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', hour12: true }),
            timeLogId: null,
            pauseLogId: null,
            isLoading: false,
            showNotesModal: false,
            showFollowModal: false,
            selectedNotes: '',
            breakDuration: 0,
            notreached: false,

        };
    },
    computed: {

        isFieldLocked() {
            return (fieldName) => this.locallockfields.includes(fieldName);
        },
        formattedReverseCountdown() {
            const minutes = Math.floor(this.ReverseCountdown / 60);
            const seconds = this.ReverseCountdown % 60;
            return minutes > 0 ? `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}` : `00:${seconds.toString().padStart(2, '0')}`;
        },
        projectOptions() {
            return [
                { label: 'vimtronix', value: 'vimtronix' },
                { label: 'XSimpress', value: 'XSimpress' },
                { label: 'box4pflege.de', value: 'box4pflege.de' },
            ];
        },
        feedbackOptions() {
            return [
                { label: 'Not interested', value: 'Not Interested' },
                { label: 'Customer is interested', value: 'Interested' },
                { label: 'Request follow-up', value: 'Follow-up' },
                { label: 'Delete address', value: 'Delete Address' },
            ];
        },
        formattedCountdown() {
            // console.log(this.countdown);
            const minutes = Math.floor(this.countdown / 60);
            const seconds = this.countdown % 60;
            return `${minutes}m ${seconds}s`;
        },
        progressValue() {
            return (this.countdown / (5 * 60)) * 100; // Assume 5 minutes as full progress for demo
        }
    },
    mounted() {
        if (this.localAddress && this.localAddress.id) {

            this.startTracking();
        }
        console.log(this.address, this.$page.props);
    },
    async beforeUnmount() {
        await axios.post(route('seen', this.localAddress.id));
    },
    methods: {
        formatnewDate(date) {
            return moment(date).format("MMM D dddd");
        },
        showNotes(notes) {
            // this.selectedNotes = notes
            this.showNotesModal = true
        },

        formatDate(data) {
            const date = new Date(data.starting_time);
            return date.toLocaleDateString();
        },
        async startTracking() {
            try {
                this.isPaused = false;
                // const response = await axios.post('/start-tracking', {
                //     address_id: this.localAddress.id,
                // });
                // this.timeLogId = response.data.id;
                this.countdown = 0;
                await this.startCountdown();
            } catch (error) {
                console.error('Error starting tracking:', error);
            }
        },
        startCountdown() {
            if (this.timer) clearInterval(this.timer);
            this.startTime = new Date().getTime();
            this.timer = setInterval(() => {
                if (!this.isPaused) {
                    this.countdown = Math.floor((new Date().getTime() - this.startTime) / 1000);
                }
            }, 1000);
            this.reversetimer = setInterval(() => {
                if (!this.isPaused) {
                    this.ReverseCountdown--;
                    if (this.ReverseCountdown < 0) {
                        this.ReverseCountdown = 0;
                        clearInterval(this.reversetimer);
                    }
                }
            }, 1000);
        },
        async togglePause() {
            this.isPaused = !this.isPaused;
            try {
                if (this.isPaused) {
                    this.pauseTime = new Date().getTime(); // Store the pause time
                    // const res = await axios.post(`/pause-tracking/${this.address.id}`);
                    // this.pauseLogId = res.data.id;
                    // console.log(this.pauseLogId);
                } else {
                    const resumeTime = new Date().getTime();
                    this.breakDuration += Math.floor((resumeTime - this.pauseTime) / 1000);
                    this.startTime += resumeTime - this.pauseTime; // Adjust the start time
                    await axios.post(route('break.end', this.localAddress.id), {
                        break_duration: this.breakDuration
                    });
                    this.breakDuration = 0;
                }
            } catch (error) {
                console.error('Error toggling pause:', error);
            }
        },
        async submitFeedback() {

            // if (!this.logdata.call_attempts || this.logdata.call_attempts <= 0) {
            //     this.$toast.add({ severity: 'error', summary: 'Error', detail: 'Please enter a positive number for call attempts', life: 4000 });
            //     return;
            // }


            this.isLoading = true;
            this.showFollowModal = false;
            try {

                if (this.localAddress.feedback != 'Follow-up') {
                    this.localAddress.follow_up_date = null
                }

                if (this.isPaused) {
                    await this.togglePause(); // Resume the tracking if it's paused
                }
                this.isPaused = true;
                clearInterval(this.timer);
                clearInterval(this.reversetimer);
                // console.log(this.countdown);

                const res = await axios.post(route('stop.tracking'), {
                    ...this.logdata,
                    address: this.localAddress,
                    total_duration: this.countdown,
                    notreached: this.notreached,
                });
                this.$toast.add({ severity: 'success', summary: 'Success', detail: 'Data saved successfully.', life: 4000 });
                console.log(res.data);
                this.localAddress = res.data.address;
                this.locallockfields = res.data.lockfields;
                if (this.localAddress && this.localAddress.company_name) {

                    this.callHistory = res.data.address.cal_logs;
                    this.timer = null;
                    this.logdata.call_attempts = null;
                    this.logdata.personal_notes = '';
                    this.logdata.interest_notes = '';
                    await this.startTracking();
                }
                this.ReverseCountdown = 180;
                this.notreached = false;
                this.isLoading = false;

            } catch (error) {
                this.notreached = false;
                this.isLoading = false;
                console.log(error);

                if (error.response?.status === 422) {
                    const errors = error.response.data.errors;
                    console.log(errors);
                    for (const field in errors) {
                        this.errors[field] = errors[field][0];
                        this.$toast.add({ severity: 'error', summary: 'Error', detail: errors[field][0], life: 4000 });
                    }
                } else {
                    console.error('Error submitting feedback:', error);
                }
            }
            // if (res.data) {
            //     this.address = res.data;
            //     this.countdown = 0;
            //     this.startTracking();
            // }
            // Save the edits
            //   await axios.post('/api/save-address', this.address);
            // console.log(res.data, 'new');
            // Fetch the next address (for demo, we'll just reset the current address)
            // this.$inertia.reload();
        },
        async submitCallbackRequest() {
            const emailMap = {
                vimtronix: 'info@vimtronix.com',
                XSimpress: 'info@xsimpress.com',
                box4pflege: 'info@box4pflege.de',
            };

            const emailAddress = emailMap[this.callbackForm.project];
            const subject = 'Rückruf bitte';
            const body = `
        Project: ${this.callbackForm.project}
        Salutation: ${this.callbackForm.salutation}
        First Name: ${this.callbackForm.firstName}
        Last Name: ${this.callbackForm.lastName}
        Phone Number: ${this.callbackForm.phoneNumber}
        Notes: ${this.callbackForm.notes}
      `;

            // Simulate sending an email
            alert(`Email sent to: ${emailAddress}\nSubject: ${subject}\nBody: ${body}`);

            // Reset form and close dialog
            this.callbackForm = {
                project: null,
                salutation: '',
                firstName: '',
                lastName: '',
                phoneNumber: '',
                notes: '',
            };
            this.showCallbackForm = false;
        },
    },
};
</script>

<style>
::-webkit-scrollbar {
    display: none;
}

.user-page {
    padding: 20px;
}

.user-actions {
    margin-bottom: 20px;
    text-align: right;
}

.address-container {
    margin-bottom: 20px;
}

.feedback-form {
    margin-bottom: 20px;
}

.call-history {
    margin-bottom: 20px;
}

.countdown-timer {
    margin-bottom: 20px;
}

.field {
    margin-bottom: 10px;
}

label {
    display: block;
    margin-bottom: 5px;
}

.collapsed {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 200px;
    /* adjust the width to your liking */
}

.p-inputtext,
.p-select,
.p-textarea {
    width: 100%;
}

#app>div>main>div>div.col-span-1.lg\:col-span-3.border.rounded-xl.shadow-xl,

#app>div>main>div>div.col-span-1.lg\:w-2\/3>div:nth-child(3),
#app>div>main>div>div.col-span-1.lg\:w-2\/3>div:nth-child(4),
#app>div>main>div>div.col-span-1.lg\:w-2\/3>div:nth-child(5) {
    background: #FFF;
}

#app>div>main>div>div.col-span-1.lg\:w-2\/3 {
    width: 100%;
}


@media only screen and (max-width: 768px) {
    #app>div>main>div>div.col-span-1.lg\:col-span-3.border.rounded-xl.shadow-xl>div.pb-2.p-4>div.inline-flex.items-center.rounded-md.bg-black.px-2.py-1.text-2xl.xl\:text-3xl.font-medium.text-white.ring-1.ring-inset.ring-black.mb-1,

    #app>div>main>div>div.col-span-1.lg\:col-span-3.border.rounded-xl.shadow-xl>div.pb-2.p-4>div.flex.flex-wrap.justify-between.items-center>div.inline-flex.items-center.rounded-md.bg-primary.px-2.py-1.text-lg.md\:text-xl.font-medium.text-white.ring-1.ring-inset.ring-primary.mb-1,

    #app>div>main>div>div.col-span-1.lg\:col-span-3.border.rounded-xl.shadow-xl>div.pb-2.p-4>div.flex.flex-wrap.justify-between.items-center>div.inline-flex.items-center.rounded-md.mr-2.mb-1.w-full.md\:w-auto {
        margin-bottom: 15px;
        margin-right: 0;
        width: 100%;
        padding: 10px 15px;
    }

    #contact_id {
        padding: 10px 15px;
    }

    #app>div>main>div>div.col-span-1.lg\:col-span-3.border.rounded-xl.shadow-xl>div.pb-2.p-4>div.flex.flex-wrap.justify-between.items-center>div.inline-flex.items-center.rounded-md.mr-2.mb-1.w-full.md\:w-auto {
        padding: 0 !important;
    }

    #app>div>main>div>div.col-span-1.lg\:col-span-3.border.rounded-xl.shadow-xl>div.flex.flex-col.lg\:flex-row.justify-center.my-4 {
        padding: 0 5px;
    }
}
</style>
