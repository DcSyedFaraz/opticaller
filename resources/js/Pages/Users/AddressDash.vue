<template>

    <Head title="Addresses" />
    <AuthenticatedLayout>


        <div class="user-page grid grid-cols-1 lg:grid-cols-5 gap-x-4 px-4" v-if="localAddress && localAddress.id">
            <!-- Main Panel -->
            <div class="col-span-1 lg:col-span-4 border rounded-xl shadow-xl">
                <div class="pb-0 p-4">
                    <div class="flex flex-wrap justify-between items-center">
                        <div
                            class="inline-flex items-center rounded-md bg-black px-2 py-1 text-2xl xl:text-sm font-medium text-white ring-1 ring-inset ring-black ">
                            Sub Project Title: {{ localAddress.subproject?.title }}
                        </div>

                        <div class="inline-flex items-center rounded-md mr-2 mb-1 w-full md:w-auto">
                            <div v-if="localAddress.subproject?.pdf_url" class="mt-2">
                                <a :href="localAddress.subproject.pdf_url" target="_blank" rel="noopener noreferrer"
                                    class="inline-flex items-center px-2 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
                                    <i class="pi pi-file-pdf "></i>
                                </a>
                            </div>
                            <!-- <div class="mt-2">
                                <Button @click="makeCall()"
                                    class="inline-flex items-center px-2 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
                                    <i class="pi pi-file-pdf "></i>
                                </Button>
                            </div> -->
                        </div>

                    </div>
                    <div class="flex flex-wrap justify-between items-center">
                        <div :class="getBorderClass(localAddress.subproject?.projects?.color)"
                            class="inline-flex items-center rounded-md  px-2 py-1 text-sm md:text-xs font-medium text-white ring-1 ring-inset  mb-1">
                            Project Title: {{ localAddress.subproject?.projects?.title }}
                        </div>
                        <!-- Existing Contact ID Field -->
                        <div class="inline-flex items-center rounded-md mr-2 mb-1 w-full md:w-auto">
                            <InputText id="contact_id" v-model="localAddress.contact_id" placeholder="Contact ID"
                                class="w-full !border-black" disabled />
                            <!-- New Button to Open Dialog -->
                            <Button icon="pi pi-search" class="ml-2" v-if="$page.props.auth.roles[0] == 'admin'"
                                @click="openContactIdDialog" v-tooltip.top="'Search Contact ID'" />
                        </div>

                    </div>


                </div>
                <div class="border m-4 lg:mx-7 rounded-lg shadow shadow-secondary my-1">
                    <!-- <div class="border-b-2 p-3">
                        <span class="mx-4 font-extrabold text-sm">Details</span>
                    </div> -->

                    <div class="grid m-3">
                        <Card class="shadow-md">
                            <template #content>
                                <div class="grid lg:grid-cols-8 grid-cols-1 gap-x-4 mb-2">
                                    <!-- Company Name Field -->
                                    <div v-if="!isHidden('company_name')"
                                        class="field col-span-1 md:col-span-2 lg:col-span-2">
                                        <label for="company_name" class="font-extrabold text-sm">
                                            Company Name: <span class="text-red-600">*</span>
                                        </label>
                                        <InputText id="company_name" v-model="localAddress.company_name"
                                            :disabled="isFieldLocked('company_name')"
                                            class="w-full !border-secondary" />
                                    </div>

                                    <!-- Salutation Field -->
                                    <div v-if="!isHidden('salutation')"
                                        class="field col-span-1 md:col-span-1 lg:col-span-1">
                                        <label for="salutation" class="font-extrabold text-sm">
                                            Salutation:
                                        </label>
                                        <Select id="salutation" v-model="localAddress.salutation"
                                            placeholder="select salutation" :options="salutationOptions"
                                            optionLabel="label" optionValue="value"
                                            :disabled="isFieldLocked('salutation')" class="w-full !border-secondary" />
                                    </div>

                                    <!-- Title Field -->
                                    <div v-if="!isHidden('titel')" class="field col-span-1 md:col-span-1 lg:col-span-1">
                                        <label for="titel" class="font-extrabold text-sm">
                                            Title:
                                        </label>
                                        <Select id="titel" v-model="localAddress.titel" placeholder="select title"
                                            :options="titelOptions" optionLabel="label" optionValue="value"
                                            :disabled="isFieldLocked('titel')" class="w-full !border-secondary" />
                                    </div>

                                    <!-- First Name Field -->
                                    <div v-if="!isHidden('first_name')"
                                        class="field col-span-1 md:col-span-1 lg:col-span-2">
                                        <label for="first_name" class="font-extrabold text-sm">
                                            First Name:
                                        </label>
                                        <InputText id="first_name" v-model="localAddress.first_name"
                                            :disabled="isFieldLocked('first_name')" class="w-full !border-secondary" />
                                    </div>

                                    <!-- Last Name Field -->
                                    <div v-if="!isHidden('last_name')" class="field md:col-span-2">
                                        <label for="last_name" class="font-extrabold text-sm">
                                            Last Name:
                                        </label>
                                        <InputText id="last_name" v-model="localAddress.last_name"
                                            :disabled="isFieldLocked('last_name')" class="w-full !border-secondary" />
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 lg:grid-cols-4 gap-x-4 mb-2">
                                    <!-- Street Address Field -->
                                    <div v-if="!isHidden('street_address')" class="field">
                                        <label class="font-extrabold text-sm" for="street_address">
                                            Street and House Number:
                                        </label>
                                        <InputText id="street_address" v-model="localAddress.street_address"
                                            :disabled="isFieldLocked('street_address')"
                                            class="w-full !border-secondary" />
                                    </div>

                                    <!-- Postal Code Field -->
                                    <div v-if="!isHidden('postal_code')" class="field">
                                        <label class="font-extrabold text-sm" for="postal_code">
                                            Postal Code:
                                        </label>
                                        <InputText id="postal_code" v-model="localAddress.postal_code"
                                            :disabled="isFieldLocked('postal_code')" class="w-full !border-secondary" />
                                    </div>

                                    <!-- City Field -->
                                    <div v-if="!isHidden('city')" class="field col-span-1 md:col-span-1">
                                        <label class="font-extrabold text-sm" for="city">
                                            City:
                                        </label>
                                        <InputText id="city" v-model="localAddress.city"
                                            :disabled="isFieldLocked('city')" class="w-full !border-secondary" />
                                    </div>

                                    <!-- Country Field -->
                                    <div v-if="!isHidden('country')" class="field">
                                        <label class="font-extrabold text-sm" for="country">
                                            Country:
                                        </label>
                                        <Select id="country" v-model="localAddress.country" filter
                                            :options="country_names" placeholder="Select a country"
                                            :disabled="isFieldLocked('country')" class="w-full !border-secondary" />
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-3 gap-x-4">
                                    <!-- Website Field -->
                                    <div v-if="!isHidden('website')" class="field">
                                        <label class="font-extrabold text-sm" for="website">
                                            Website:
                                        </label>
                                        <InputText id="website" v-model="localAddress.website"
                                            :disabled="isFieldLocked('website')" class="w-full !border-secondary" />
                                    </div>

                                    <!-- Phone Number Field -->
                                    <!-- Phone Number Field with Call Button -->
                                    <div v-if="!isHidden('phone_number')" class="field">
                                        <label class="font-extrabold text-sm mr-2" for="phone_number">
                                            Phone Number:
                                        </label>
                                        <div class="flex items-center">

                                            <InputText id="phone_number" v-model="localAddress.phone_number"
                                                :disabled="isFieldLocked('phone_number')"
                                                class="w-full !border-secondary" />
                                            <Button icon="pi pi-phone" class="ml-2" @click="triggerCallOnNewRecord" />
                                        </div>
                                    </div>


                                    <!-- Email Address Field -->
                                    <div v-if="!isHidden('email_address_system')" class="field">
                                        <label for="email_address_system" class="font-extrabold text-sm">
                                            Email Address: <span class="text-red-600">*</span>
                                        </label>
                                        <InputText id="email_address_system" v-model="localAddress.email_address_system"
                                            :disabled="isFieldLocked('email_address_system')"
                                            class="w-full !border-secondary" />
                                    </div>
                                </div>
                                <hr class="mb-2 border-1 border-black">
                                <div class="grid">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-4">
                                        <!-- Feedback Field -->
                                        <div v-if="!isHidden('feedback')" class="field md:col-span-2">
                                            <label class="font-extrabold text-sm" for="feedback">
                                                Feedback:
                                            </label>
                                            <Select id="feedback" v-model="localAddress.feedback"
                                                class="w-full !border-secondary" :options="feedbackOptions"
                                                optionValue="value" optionLabel="label"
                                                :disabled="isFieldLocked('feedback')" />
                                        </div>
                                        <!-- Interest Notes Field -->
                                        <div v-if="!isHidden('interest_notes')" class="field">
                                            <label class="font-extrabold text-sm" for="interest_notes">
                                                Interest Notes:
                                            </label>
                                            <Textarea id="interest_notes" v-model="logdata.interest_notes" rows="2"
                                                class="w-full !border-secondary"
                                                :disabled="isFieldLocked('interest_notes')" />
                                        </div>
                                        <!-- Personal Notes Field -->
                                        <div v-if="!isHidden('personal_notes')" class="field">
                                            <label class="font-extrabold text-sm" for="personal_notes">
                                                Personal Notes:
                                            </label>
                                            <Textarea id="personal_notes" v-model="logdata.personal_notes" rows="2"
                                                class="w-full !border-secondary"
                                                :disabled="isFieldLocked('personal_notes')" />
                                        </div>
                                    </div>
                                </div>
                                <div class="flex justify-center flex-wrap my-1">
                                    <button @click="notreached = true; submitFeedback()" :disabled="isButtonDisabled"
                                        class="bg-primary justify-center text-white flex px-[3rem] w-full lg:w-auto py-3 text-xl mx-2 rounded mb-2 disabled:cursor-not-allowed">
                                        <svg width="25" height="25" viewBox="0 0 25 25" class="my-1" fill="none"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <path
                                                d="M22.4383 24.261C19.9103 24.261 17.3183 23.628 14.6623 22.362C12.0063 21.096 9.53434 19.3185 7.24634 17.0295C4.97634 14.7415 3.20833 12.274 1.94233 9.62705C0.676335 6.98005 0.043335 4.39305 0.043335 1.86605C0.043335 1.41605 0.193335 1.03605 0.493335 0.726047C0.793335 0.416047 1.16834 0.261047 1.61834 0.261047H5.32783C5.73583 0.261047 6.09183 0.389547 6.39583 0.646547C6.69983 0.903547 6.90533 1.23305 7.01233 1.63505L7.75634 5.21105C7.82634 5.63105 7.81383 5.99755 7.71883 6.31055C7.62383 6.62355 7.45734 6.88005 7.21934 7.08005L3.92834 10.149C4.54434 11.266 5.22484 12.304 5.96984 13.263C6.71484 14.222 7.50984 15.1305 8.35484 15.9885C9.22484 16.8585 10.1618 17.6685 11.1658 18.4185C12.1698 19.1675 13.2718 19.8745 14.4718 20.5395L17.6803 17.274C17.9243 17.011 18.1988 16.8375 18.5038 16.7535C18.8078 16.6705 19.1448 16.654 19.5148 16.704L22.6693 17.349C23.0773 17.449 23.4083 17.6545 23.6623 17.9655C23.9163 18.2765 24.0433 18.6325 24.0433 19.0335V22.686C24.0433 23.136 23.8883 23.511 23.5783 23.811C23.2683 24.111 22.8883 24.261 22.4383 24.261Z"
                                                fill="white" />
                                            <path d="M15.0433 2.26105L20.3483 7.56605M20.3483 2.26105L15.0433 7.56605"
                                                stroke="white" stroke-width="2" stroke-linecap="round"
                                                stroke-linejoin="round" />
                                        </svg>
                                        <span class="mx-2 my-1">Not Reached</span>
                                    </button>
                                    <button @click="showFollowModal = true" :disabled="isButtonDisabled"
                                        class="bg-[#383838] justify-center hover:bg-[#161616] disabled:bg-[#464545] disabled:cursor-not-allowed text-white flex px-[3rem] w-full lg:w-auto py-3 text-xl mx-2 rounded mb-2">
                                        <svg width="33" height="33" viewBox="0 0 33 33" fill="none"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <path d="M1.60718 1.66168V18.2422H25.4797V1.66168" stroke="white"
                                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                            <path d="M17.5222 22.8817L13.5434 26.8604L9.5647 22.8817" stroke="white"
                                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                            <path d="M13.5435 18.2398V26.8604" stroke="white" stroke-width="2"
                                                stroke-linecap="round" stroke-linejoin="round" />
                                            <path d="M16.1885 5.62616L19.5116 8.95604L16.1885 12.3315" stroke="white"
                                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                            <path d="M7.57593 8.95673H19.5116" stroke="white" stroke-width="2"
                                                stroke-linecap="round" />
                                        </svg>
                                        <span class="mx-2 my-1">Follow-Ups</span>
                                    </button>
                                    <button @click="saveEdits = true; submitFeedback()" :disabled="isButtonDisabled"
                                        class="bg-secondary justify-center hover:bg-secondary/75 disabled:bg-secondary/75 disabled:cursor-not-allowed text-white flex px-[3rem] w-full lg:w-auto py-3 text-xl mx-2 rounded mb-2">
                                        <i class="pi pi-save !text-2xl"></i>
                                        <span class="mx-2 my-1 text-center">Save & Next</span>
                                    </button>
                                </div>
                            </template>
                        </Card>
                    </div>

                </div>
                <!-- Notes and Buttons remain unchanged as they have flexible widths -->
                <!-- <div class="border m-4 my-1 lg:mx-7 rounded-lg shadow shadow-blue-200">

                    <div class="grid">
                        <Card class="shadow-md">
                            <template #content>
                            </template>
                        </Card>
                    </div>
                </div> -->
                <!-- Call History remains unchanged with the addition of responsive adjustments -->
                <div class="call-history lg:col-span-1 hidden">
                    <Card class="shadow-md">
                        <template #title>
                            <h2 class="text-sm font-bold">Call History</h2>
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

                <Link :href="route('callback')" class="p-button p-component p-button-lg w-full my-2 !rounded">
                <i class="pi pi-phone !text-xl"></i> Callback
                </Link>
                <Button @click="togglePause" :label="isPaused ? 'End Break' : 'Take Break'"
                    :class="isPaused ? '!bg-red-500 !border-red-500' : '!bg-secondary !border-secondary'"
                    class="w-full my-2 !rounded" size="large">
                    <template #icon>
                        <svg v-if="!isPaused" width="26" height="23" viewBox="0 0 26 37" fill="none"
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
                <div class="field col-span-1 md:col-span-1 lg:col-span-1">
                    <label for="notes" class="!font-extrabold text-sm">
                        Notes:
                    </label>
                    <InputText id="notes" v-model="localAddress.notes" class="w-full !border-secondary" disabled />
                    <!-- <p
                        class="text-sm text-gray-700 font-bold overflow-hidden text-ellipsis whitespace-nowrap max-w-[200px]">
                        {{ localAddress.notes }}
                    </p> -->
                </div>
                <div class="bg-white border border-gray-300 rounded-lg shadow-xl">
                    <div class="  pb-2 border-b-2 p-4">
                        <div class="grid grid-cols-1">
                        </div>
                        <div class="field col-span-1 md:col-span-1 lg:col-span-1 flex justify-between items-center">

                            <h3 class="text-sm font-semibold text-gray-800">Call History</h3>
                            <button class="text-gray-500 hover:text-gray-700 focus:outline-none" @click="showNotes()">
                                <i class="pi pi-expand !text-xl text-secondary"></i>
                            </button>
                        </div>
                    </div>
                    <div class="my-3" v-for="(item, index) in callHistory.slice(0, 3)" :key="index"
                        v-if="callHistory.length > 0">
                        <div class="px-4">
                            <span
                                class="inline-flex items-center rounded-md bg-secondary px-2 py-1 text-[10px] font-medium text-white ring-1 ring-inset ring-secondary mb-1">
                                Last call on: {{ formatnewDate(item.created_at) }}
                            </span>
                            <!-- <span
                                class="inline-flex mx-2 items-center rounded-md bg-primary px-2 py-1 text-[10px] font-medium text-white ring-1 ring-inset ring-primary">
                                Country: {{ localAddress.country }}
                            </span> -->
                            <span
                                class="inline-flex items-center rounded-md bg-[#3E3E3E] my-1 px-2 py-1 text-[10px] font-medium text-white ring-1 ring-inset ring-[#3E3E3E]">
                                Agent: {{ item.users?.name }}
                            </span>
                        </div>
                        <div class="border-b border-gray-300 pt-2 p-4">
                            <div v-if="item.feedback" class="mb-4">
                                <div class="flex items-center mb-1">
                                    <span class="text-md font-[1000]">Feedback:</span>
                                </div>
                                <p
                                    class="text-sm text-gray-700 font-bold overflow-hidden text-ellipsis whitespace-nowrap max-w-[200px]">
                                    {{ item.feedback }}
                                </p>
                            </div>
                            <div v-if="item.sub_project_id" class="mb-4">
                                <div class="flex items-center mb-1">
                                    <span class="text-md font-[1000]">Sub Project:</span>
                                </div>
                                <p
                                    class="text-sm text-gray-700 font-bold overflow-hidden text-ellipsis whitespace-nowrap max-w-[200px]">
                                    {{ item.sub_project_id }}
                                </p>
                            </div>
                            <div v-if="item.notes?.personal_notes" class="mb-4">
                                <div class="flex items-center mb-1">
                                    <span class="text-md font-[1000]">Personal Notes:</span>
                                </div>
                                <p class="text-sm text-gray-700 font-bold overflow-hidden max-w-[200px]">
                                    {{ item.notes?.personal_notes }}
                                </p>
                            </div>

                            <div v-if="item.notes?.interest_notes" class="mb-4">
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
                                        item.call_duration < 60 ? item.call_duration + ' Seconds' :
                                            Math.floor(item.call_duration / 60) + ' Minutes ' + (item.call_duration % 60)
                                            + ' Seconds' }} </span>
                                </span>
                                <span
                                    class="inline-flex items-center rounded-md bg-[#A6A2A0] my-1 px-2 py-1 text-[10px] font-medium text-white ring-1 ring-inset ring-[#A6A2A0]">
                                    On Page Duration:
                                    <span class="font-bold">
                                        {{
                                            item.total_duration < 60 ? item.total_duration + ' Seconds' :
                                                Math.floor(item.total_duration / 60) + ' Minutes ' + (item.total_duration %
                                                    60) + ' Seconds' }} </span>
                                    </span>
                        </div>
                    </div>
                    <div v-else>
                        <div class="flex justify-center items-center h-[12rem]">
                            <div class="text-sm text-gray-500">No history</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- New Dialog for Changing Contact ID -->
            <Dialog header="Change Contact ID" v-model:visible="showContactIdDialog" modal class="rounded-lg shadow-lg"
                :style="{ width: '90%', maxWidth: '36rem' }">
                <div class="p-6 space-y-4">
                    <div class="space-y-2">
                        <!-- Input Field for New Contact ID -->
                        <div class="field">
                            <label for="new_contact_id">New Contact ID:</label>
                            <InputText id="new_contact_id" v-model="newContactId" placeholder="Enter new Contact ID"
                                class="w-full !border-secondary" />
                            <!-- Error Message Display -->
                            <small class="text-red-600" v-if="contactIdError">{{ contactIdError }}</small>
                        </div>
                        <div class="field">
                            <label for="new_contact_id">New Sub-Project:</label>
                            <Select id="subproject" v-model="newsubproject" placeholder="select subproject"
                                :options="subproject" optionLabel="title" optionValue="id"
                                class="w-full !border-secondary" />
                            <!-- Error Message Display -->
                            <small class="text-red-600" v-if="subprojectError">{{ subprojectError }}</small>
                        </div>
                    </div>
                    <!-- Action Buttons -->
                    <div class="flex justify-end">
                        <Button label="Cancel" class="mr-2" @click="closeContactIdDialog" />
                        <Button label="Submit" @click="submitNewContactId" :loading="contactIdLoading" />
                    </div>
                </div>
            </Dialog>
            <!-- Dialog components remain unchanged for responsiveness -->
            <Dialog header="Follow Up" v-model:visible="showFollowModal" modal class="rounded-lg shadow-lg"
                :style="{ width: '90%', maxWidth: '36rem' }">
                <div class="p-6 space-y-4">
                    <div class="space-y-2">
                        <!-- Date Selection -->
                        <div class="field">
                            <label for="follow_up_date">Follow-up Date:</label>
                            <DatePicker id="follow_up_date" v-model="selectedDate" dateFormat="dd/mm/yy"
                                placeholder="Select follow-up date" class="w-full !border-secondary" :showTime="false"
                                :manualInput="false" />
                            <small class="text-red-600" v-if="errors.follow_up_date">{{ errors.follow_up_date }}</small>
                        </div>

                        <!-- Hour Selection -->
                        <div class="field">
                            <label for="follow_up_hour">Hour:</label>
                            <Select id="follow_up_hour" v-model="selectedHour" :options="hourOptions"
                                optionLabel="label" optionValue="value" placeholder="Select hour"
                                class="w-full !border-secondary" />
                            <small class="text-red-600" v-if="errors.follow_up_hour">{{ errors.follow_up_hour }}</small>
                        </div>

                        <!-- Minute Selection -->
                        <div class="field">
                            <label for="follow_up_minute">Minute:</label>
                            <Select id="follow_up_minute" v-model="selectedMinute" :options="minuteOptions"
                                optionLabel="label" optionValue="value" placeholder="Select minute"
                                class="w-full !border-secondary" />
                            <small class="text-red-600" v-if="errors.follow_up_minute">{{ errors.follow_up_minute
                                }}</small>
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <Button class="font-bold py-2 px-4" @click="scheduleFollowUp">Schedule Follow-up</Button>
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
                        <!-- <span
                            class="inline-flex items-center rounded-md bg-primary mx-2 px-2 py-1 text-[12px] font-large text-white ring-1 ring-inset ring-primary">
                            Country: {{ localAddress.country }}
                        </span> -->
                        <span
                            class="inline-flex items-center rounded-md bg-[#3E3E3E] mx-2 my-1 px-2 py-1 text-[12px] font-large text-white ring-1 ring-inset ring-[#3E3E3E]">
                            Agent: {{ item.users?.name }}
                        </span>
                    </div>
                    <div class="border-b border-gray-300 pt-2 p-4">
                        <div v-if="item.feedback" class="mb-4">
                            <div class="flex items-center mb-1">
                                <span class="text-md font-[1000]">Feedback:</span>
                            </div>
                            <p
                                class="text-sm text-gray-700 font-bold overflow-hidden text-ellipsis whitespace-nowrap max-w-[200px]">
                                {{ item.feedback }}
                            </p>
                        </div>
                        <div v-if="item.sub_project_id" class="mb-4">
                            <div class="flex items-center mb-1">
                                <span class="text-md font-[1000]">Sub Project:</span>
                            </div>
                            <p
                                class="text-sm text-gray-700 font-bold overflow-hidden text-ellipsis whitespace-nowrap max-w-[200px]">
                                {{ item.sub_project_id }}
                            </p>
                        </div>
                        <div class="mb-4">
                            <div class="flex items-center mb-1">
                                <!-- <span class="h-2 w-2 bg-primary rounded-full mr-2"></span> -->
                                <span class="text-md font-[1000] ">Personal Notes:</span>
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
                            class="inline-flex items-center rounded-md bg-[#A6A2A0] my-1 mr-2 px-2 py-1 text-[10px] font-medium text-white ring-1 ring-inset ring-[#A6A2A0]">
                            On Page Duration:
                            <span class="font-bold">
                                {{
                                    item.total_duration < 60 ? item.total_duration + ' Seconds' :
                                        Math.floor(item.total_duration / 60) + ' Minutes ' + (item.total_duration % 60)
                                        + ' Seconds' }} </span>
                            </span>
                            <span
                                class="inline-flex items-center rounded-md bg-[#A6A2A0] my-1 px-2 py-1 text-[10px] font-medium text-white ring-1 ring-inset ring-[#A6A2A0]">
                                Call Duration:
                                <span class="font-bold">
                                    {{
                                        item.call_duration < 60 ? item.call_duration + ' Seconds' :
                                            Math.floor(item.call_duration / 60) + ' Minutes ' + (item.call_duration % 60)
                                            + ' Seconds' }} </span>
                                </span>
                    </div>
                </div>
            </Dialog>

            <Dialog header="Project Change Notification" v-model:visible="projectChanged" modal
                class="rounded-lg shadow-lg">
                <div class="p-6">
                    <p class="font-semibold">
                        The project has changed. Please ensure all related information is updated accordingly.
                    </p>
                    <div class="flex justify-end">
                        <Button label="OK" @click="projectChanged = false" class="mt-3" />
                    </div>
                </div>
            </Dialog>

            <!-- Twilio Call Component -->
            <TwilioCallComponent :phoneNumber="formattedPhoneNumber" ref="twilioCallComponent" :isPaused="isPaused"
                @incoming-call="handleIncomingCall" @call-connected="handleCallConnected"
                @call-accepted="startCallDurationTimer" @call-disconnected="handleCallDisconnected"
                @twilio-error="handleTwilioError" />

            <!-- Incoming Call Dialog -->
            <Dialog header="Incoming Call" v-model:visible="showIncomingCallDialog" :closable="false" draggable
                resizable class="w-11/12 md:w-1/3">
                <div class="flex flex-col items-center">
                    <p class="text-lg">Incoming call from: {{ incomingCallFrom }}</p>
                    <div class="flex space-x-4 mt-4">
                        <Button label="Accept" icon="pi pi-check" class="p-button-success"
                            @click="acceptIncomingCall" />
                        <Button label="Reject" icon="pi pi-times" class="p-button-danger" @click="rejectIncomingCall" />
                    </div>
                </div>
            </Dialog>

            <!-- Active Call Dialog -->
            <!-- <Dialog header="Call in Progress" v-model:visible="showActiveCallDialog" draggable resizable
                :closable="false" class="w-11/12 md:w-1/3">
                <div class="flex flex-col items-center">
                    <p class="text-lg">Calling: {{ activeCallNumber }}</p>
                    <div class="flex space-x-4 mt-4">
                        <Button label="Hang Up" icon="pi pi-phone-slash" class="p-button-danger" @click="hangUp" />
                    </div>
                </div>
            </Dialog> -->
            <Dialog header="Call in Progress" v-model:visible="showActiveCallDialog" :closable="false"
                class="w-11/12 md:w-1/3 p-2">
                <template #header>
                    <div class="w-full">
                      <!-- Draggable Bar -->
                      <div
                        class="w-full h-4 bg-gray-400 cursor-move mb-2 rounded-t"
                        style="user-select: none;"
                      ></div>

                      <!-- Header Content -->
                      <div
                        class="flex items-center justify-between bg-gray-100 px-4 py-2 rounded-b cursor-move"
                        style="user-select: none;"
                      >
                        <span class="font-semibold text-gray-800">Call in Progress</span>
                        <i class="pi pi-bars text-gray-600"></i>
                      </div>
                    </div>
                  </template>
                <div class="flex flex-col items-center space-y-4">
                    <!-- Call Information -->
                    <div class="flex items-center space-x-2">
                        <i class="pi pi-phone text-3xl text-blue-500"></i>
                        <p class="text-lg font-semibold">Calling: {{ activeCallNumber }}</p>
                    </div>

                    <!-- Call Duration (Visible After Call is Accepted) -->
                    <div class="flex items-center space-x-2">
                        <i class="pi pi-clock text-2xl text-gray-600"></i>
                        <p class="text-md font-medium">Duration: {{ formattedCallDuration }}</p>
                    </div>

                    <!-- Hang Up Button -->
                    <div class="flex space-x-4 mt-6">
                        <Button label="Hang Up" icon="pi pi-phone-slash" class="p-button-danger p-button-rounded"
                            @click="hangUp" />
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
            <p class="font-semibold text-red-500" v-if="this.localAddress.original?.warning">
                {{ this.localAddress.original.warning }}
            </p>
            <p class="font-semibold" v-else>
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
import timezone from 'moment-timezone';
// import country from 'country-list-js';
import TwilioCallComponent from './TwilioCallComponent.vue';
// import TwilioCallComponent from './CloudTalkCallComponent.vue';

export default {
    components: { TwilioCallComponent },
    props: {
        address: Object,
        subproject: Object,
        lockfields: Array,
    },
    data() {
        return {
            callDuration: 0,
            callDurationTimer: null,
            showIncomingCallDialog: false,
            incomingCallFrom: "",
            showActiveCallDialog: false,
            activeCallNumber: "",
            localAddress: { ...this.address },
            locallockfields: this.lockfields ? [...this.lockfields] : [],
            callHistory: [...(this.address?.cal_logs ?? [])],
            logdata: {},
            errors: {},
            form: {},
            countdown: 0,
            ReverseCountdown: this.address.subproject?.reverse_countdown, // Initialize dynamically
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
            country_names: ['Germany', 'Austria', 'Switzerland', 'France', 'Italy'],
            breakDuration: 0,
            notreached: false,
            saveEdits: false,
            salutationOptions: [
                { label: 'Herr', value: 'Herr' },
                { label: 'Frau', value: 'Frau' },
                { label: 'Divers', value: 'Divers' },
                { label: 'Sehr geehrte Damen und Herren', value: 'Sehr geehrte Damen und Herren' },
            ],
            titelOptions: [
                { label: 'Dr.', value: 'Dr.' },
                { label: 'Prof.', value: 'Prof.' },
                { label: 'Prof. Dr.', value: 'Prof. Dr.' },
            ],
            previousProject: '',
            projectChanged: false,
            isButtonDisabled: false,
            // New data properties for follow-up scheduling
            selectedDate: null,
            selectedHour: null,
            selectedMinute: null,
            showContactIdDialog: false,
            newContactId: '',
            newsubproject: '',
            contactIdLoading: false,
            contactIdError: '',
            subprojectError: '',
            hourOptions: [
                { label: '09', value: 9 },
                { label: '10', value: 10 },
                { label: '11', value: 11 },
                { label: '12', value: 12 },
                { label: '13', value: 13 },
                { label: '14', value: 14 },
                { label: '15', value: 15 },
                { label: '16', value: 16 },
                { label: '17', value: 17 },
            ],
            minuteOptions: [
                { label: '00', value: '00' },
                { label: '15', value: '15' },
                { label: '30', value: '30' },
                { label: '45', value: '45' },
            ],
        };
    },
    computed: {
        formattedPhoneNumber() {
            return this.formatPhoneNumber(this.localAddress.phone_number);
        },
        formattedCallDuration() {
            const minutes = Math.floor(this.callDuration / 60);
            const seconds = this.callDuration % 60;
            return `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
        },
        isFieldLocked() {
            return (fieldName) => this.locallockfields.includes(fieldName);
        },
        isHidden() {
            return (fieldName) => {
                return this.localAddress.subproject?.field_visibilities?.some(
                    (visibility) => visibility.field_name === fieldName && visibility.is_hidden === 1
                ) ?? false; // Default to false if undefined
            };
        },
        formattedReverseCountdown() {
            const minutes = Math.floor(this.ReverseCountdown / 60);
            const seconds = this.ReverseCountdown % 60;
            return minutes > 0
                ? `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`
                : `00:${seconds.toString().padStart(2, '0')}`;
        },
        projectOptions() {
            return [
                { label: 'vimtronix', value: 'vimtronix' },
                { label: 'XSimpress', value: 'XSimpress' },
                { label: 'box4pflege.de', value: 'box4pflege.de' },
            ];
        },
        feedbackOptions() {
            if (this.localAddress.subproject && this.localAddress.subproject.feedbacks) {
                return this.localAddress.subproject.feedbacks.map(fb => ({
                    label: fb.label,
                    value: fb.value,
                }));
            }
            return [
                { label: 'Not interested', value: 'Not Interested' },
                { label: 'Customer is interested', value: 'Interested' },
                { label: 'Request follow-up', value: 'Follow-up' },
                { label: 'Delete address', value: 'Delete Address' },
            ];
        },
        formattedCountdown() {
            const minutes = Math.floor(this.countdown / 60);
            const seconds = this.countdown % 60;
            return `${minutes}m ${seconds}s`;
        },
        progressValue() {
            return (this.countdown / (5 * 60)) * 100; // Assume 5 minutes as full progress for demo
        }
    },
    watch: {
        'localAddress.subproject': {
            handler(newSubproject) {
                console.log(newSubproject);
                this.ReverseCountdown = newSubproject?.reverse_countdown || 180;
                this.resetReverseCountdown();
            },
            deep: true,
        },
    },
    beforeDestroy() {
        if (this.callDurationTimer) {
            clearInterval(this.callDurationTimer);
            this.callDurationTimer = null;
        }
        if (this.reversetimer) {
            clearInterval(this.reversetimer);
            this.reversetimer = null;
        }
    },
    mounted() {
        if (this.localAddress && this.localAddress.id) {
            this.ReverseCountdown = this.localAddress.subproject?.reverse_countdown || 180;
            this.startTracking();
            this.reverseCountdownFunc();
            this.localAddress.feedback = '';
            this.localAddress.follow_up_date = null;
            this.previousProject = this.localAddress.subproject?.projects?.title;
        }
    },
    methods: {
        formatPhoneNumber(number) {
            const cleaned = ('' + number).replace(/\D/g, '');

            if (cleaned.length > 10) {
                return `+${cleaned}`;
            }

            if (cleaned.length === 10) {
                return `+1${cleaned}`; // Default to US country code
            }

            return number;
        },
        convertSeconds(seconds) {
            if (seconds === null || seconds === 0) {
                return '0 seconds';
            }

            // Ensure the input is an integer
            seconds = parseInt(seconds);

            const hours = Math.floor(seconds / 3600);
            const minutes = Math.floor((seconds % 3600) / 60);
            const remainingSeconds = seconds % 60;

            let parts = [];

            if (hours > 0) {
                parts.push(`${hours} hour${hours > 1 ? 's' : ''}`);
            }

            if (minutes > 0) {
                parts.push(`${minutes} minute${minutes > 1 ? 's' : ''}`);
            }

            if (remainingSeconds > 0) {
                parts.push(`${remainingSeconds} second${remainingSeconds > 1 ? 's' : ''}`);
            }

            return parts.join(' ');
        },
        triggerCallOnNewRecord() {
            if (this.$refs.twilioCallComponent && this.formattedPhoneNumber) {
                clearInterval(this.reversetimer);
                this.ReverseCountdown = this.localAddress.subproject?.reverse_countdown || 180;
                this.$refs.twilioCallComponent.triggerCall();
                this.reverseCountdownFunc();
            }
        },
        startCallDurationTimer() {
            this.callDuration = 0; // Reset duration at the start of a call
            this.callDurationTimer = setInterval(() => {
                this.callDuration += 1;
            }, 1000);
        },
        /**
         * Stops the call duration timer.
         */
        stopCallDurationTimer() {
            if (this.callDurationTimer) {
                clearInterval(this.callDurationTimer);
                this.callDurationTimer = null;
            }
        },
        handleIncomingCall(payload) {
            this.incomingCallFrom = payload.from;
            this.showIncomingCallDialog = true;
            this.activeConnection = payload.connection;
        },
        handleCallConnected(toNumber) {
            this.activeCallNumber = toNumber;
            this.showActiveCallDialog = true;
            this.callDuration = 0;
            // this.startCallDurationTimer();
        },
        handleCallDisconnected() {
            this.showActiveCallDialog = false;
            this.activeCallNumber = "";
            this.stopCallDurationTimer();
        },
        handleTwilioError(errorMessage) {
            this.$toast.add({
                severity: "error",
                summary: "Twilio Error",
                detail: errorMessage,
                life: 4000,
            });
        },
        acceptIncomingCall() {
            if (this.activeConnection) {
                this.activeConnection.accept();
                this.showIncomingCallDialog = false;
                this.activeConnection = null;
            }
        },
        rejectIncomingCall() {
            if (this.activeConnection) {
                this.activeConnection.reject();
                this.showIncomingCallDialog = false;
                this.activeConnection = null;
            }
        },
        hangUp() {
            this.$refs.twilioCallComponent.hangUp();
            this.resetReverseCountdown();
            this.stopCallDurationTimer();
            this.showActiveCallDialog = false;
            this.activeCallNumber = "";
        },
        openContactIdDialog() {
            this.newContactId = '';
            this.newsubproject = '';
            this.contactIdError = '';
            this.subprojectError = '';
            this.showContactIdDialog = true;
        },
        // Method to close the Contact ID dialog
        closeContactIdDialog() {
            this.showContactIdDialog = false;
        },
        async makeCall() {
            try {
                const response = await axios.post(route('call'), {
                    phone_number: this.localAddress.phone_number,
                });
                console.log('Call SID:', response.data);
            } catch (error) {
                console.error('Error making call:', error);
            }
        },
        // Method to handle the submission of the new Contact ID
        async submitNewContactId() {
            if (!this.newContactId) {
                this.contactIdError = 'Contact ID is required.';
                return;
            }
            if (!this.newsubproject) {
                this.subprojectError = 'Sub Project ID is required.';
                return;
            }

            this.contactIdLoading = true;
            this.contactIdError = '';
            this.subprojectError = '';

            try {
                const response = await axios.get(route('address.getByContactId', { contact_id: this.newContactId, sub_project_id: this.newsubproject }));

                if (response.data && response.data.address) {
                    console.log(response.data);

                    this.localAddress = response.data.address;
                    this.locallockfields = response.data.lockfields || [];
                    this.callHistory = response.data.address.cal_logs || [];
                    this.previousProject = this.localAddress.subproject?.projects?.title || '';

                    clearInterval(this.timer);
                    clearInterval(this.reversetimer);
                    this.$nextTick(() => {
                        this.triggerCallOnNewRecord();
                    });
                    this.countdown = 0;
                    this.ReverseCountdown = this.localAddress.subproject?.reverse_countdown || 180;

                    await this.startTracking();

                    this.$toast.add({
                        severity: 'success',
                        summary: 'Success',
                        detail: 'Address loaded successfully.',
                        life: 3000
                    });

                    this.showContactIdDialog = false;
                } else {
                    this.contactIdError = 'No address found for the provided Contact ID.';
                }
            } catch (error) {
                console.error('Error fetching address:', error);
                if (error.response && error.response.data && error.response.data.error) {
                    this.contactIdError = error.response.data.error;
                } else {
                    this.contactIdError = 'An error occurred while fetching the address.';
                }
            } finally {
                this.contactIdLoading = false;
            }
        },
        getBorderClass(color) {
            const colorMap = {
                '#509EE9': 'ring-[#509EE9] bg-[#509EE9]',
                '#2CA77F': 'ring-[#2CA77F] bg-[#2CA77F]',
                '#A72C53': 'ring-[#A72C53] bg-[#A72C53]',
                '#6A1C5C': 'ring-[#6A1C5C] bg-[#6A1C5C]',
                '#2D0C17': 'ring-[#2D0C17] bg-[#2D0C17]',
                '#1C356A': 'ring-[#1C356A] bg-[#1C356A]',
                '#EC1E85': 'ring-[#EC1E85] bg-[#EC1E85]',
                '#4757BC': 'ring-[#4757BC] bg-[#4757BC]',
                '#ED6659': 'ring-[#ED6659] bg-[#ED6659]',
                '#E4B53E': 'ring-[#E4B53E] bg-[#E4B53E]',
                '#5C6A1C': 'ring-[#5C6A1C] bg-[#5C6A1C]',
                '#6E0B0B': 'ring-[#6E0B0B] bg-[#6E0B0B]',
            };

            return colorMap[color] || 'bg-primary ring-primary';
        },
        async scheduleFollowUp() {
            if (!this.selectedDate || this.selectedHour === null || this.selectedMinute === null) {
                this.$toast.add({ severity: 'error', summary: 'Error', detail: 'Please select date, hour, and minute.', life: 4000 });
                return;
            }

            let followUpMoment = timezone(this.selectedDate);

            followUpMoment.hour(this.selectedHour);
            followUpMoment.minute(this.selectedMinute);
            followUpMoment.second(0);

            const followUpDateTime = followUpMoment.toDate();

            console.log('Selected Date:', this.selectedDate);
            console.log('Selected Hour:', this.selectedHour);
            console.log('Selected Minute:', this.selectedMinute);
            console.log('Combined Follow-Up DateTime:', followUpDateTime);

            this.localAddress.follow_up_date = followUpDateTime;

            await this.submitFeedback();
        },
        formatnewDate(date) {
            const userLocale = 'de';

            const options = {
                year: 'numeric',
                month: '2-digit',
                day: '2-digit',
                hour: '2-digit',
                minute: '2-digit',
                hour12: false, // 24-hour format
            };

            const formatter = new Intl.DateTimeFormat(userLocale, options);

            return formatter.format(new Date(date));
        },
        showNotes(notes) {
            this.showNotesModal = true;
            this.selectedNotes = notes;
        },
        formatDate(data) {
            const date = new Date(data.starting_time);
            return date.toLocaleDateString();
        },
        async startTracking() {
            try {
                this.isPaused = false;
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
        },
        reverseCountdownFunc() {
            if (this.reversetimer) clearInterval(this.reversetimer);
            this.reversetimer = setInterval(() => {
                if (!this.isPaused && this.ReverseCountdown > 0) {
                    this.ReverseCountdown--;
                    if (this.ReverseCountdown <= 0) {
                        this.ReverseCountdown = 0;
                        clearInterval(this.reversetimer);
                        // Optionally, trigger some action when countdown reaches zero
                        this.$toast.add({
                            severity: 'info',
                            summary: 'Countdown Finished',
                            detail: 'Reverse countdown has reached zero.',
                            life: 6000,
                        });
                        this.saveEdits = true;
                        this.submitFeedback();
                    }
                }
            }, 1000);
        },
        resetReverseCountdown() {
            clearInterval(this.reversetimer);
            this.ReverseCountdown = this.localAddress.subproject?.reverse_countdown || 180;
            this.reverseCountdownFunc();
        },
        async togglePause() {
            this.isPaused = !this.isPaused;
            try {
                if (this.isPaused) {
                    this.pauseTime = new Date().getTime(); // Store the pause time
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
            this.isButtonDisabled = true;
            setTimeout(() => {
                this.isButtonDisabled = false;
            }, 5000);

            this.isLoading = true;
            this.showFollowModal = false;
            try {
                if (this.isPaused) {
                    await this.togglePause(); // Resume the tracking if it's paused
                }

                const res = await axios.post(route('stop.tracking'), {
                    ...this.logdata,
                    address: this.localAddress,
                    call_duration: this.callDuration,
                    total_duration: this.countdown,
                    notreached: this.notreached,
                    save_edits: this.saveEdits,
                });
                this.notreached = false;
                this.saveEdits = false;
                if (res.data.limit) {
                    this.$toast.add({ severity: 'success', summary: 'Success', detail: '🚫 We tried to reach this address 10 times without success. Please contact the admin for further assistance.', life: 6000 });
                } else {
                    this.$toast.add({ severity: 'success', summary: 'Success', detail: 'Data saved successfully.', life: 4000 });
                }
                console.log(res.data);
                clearInterval(this.timer);
                clearInterval(this.reversetimer);
                this.localAddress = res.data.address;
                this.triggerCallOnNewRecord();

                const newProjectTitle = this.localAddress.subproject?.projects?.title;

                console.log(newProjectTitle, this.previousProject);

                if (this.previousProject && newProjectTitle !== this.previousProject) {
                    this.projectChanged = true;
                }
                this.previousProject = newProjectTitle;
                this.locallockfields = res.data.lockfields;
                if (this.localAddress && this.localAddress.id) {
                    this.callHistory = res.data.address.cal_logs;
                    this.timer = null;
                    this.logdata.call_attempts = null;
                    this.logdata.personal_notes = '';
                    this.localAddress.feedback = '';
                    this.logdata.interest_notes = '';
                    await this.startTracking();
                }
                this.ReverseCountdown = this.localAddress.subproject?.reverse_countdown || 180;
                this.notreached = false;
                this.isLoading = false;
                this.localAddress.follow_up_date = null;
            } catch (error) {
                this.notreached = false;
                this.saveEdits = false;
                this.isLoading = false;
                this.localAddress.follow_up_date = null;
                console.log(error);

                if (error.response.data.error) {
                    this.$toast.add({ severity: 'error', summary: 'Error', detail: error.response.data.error, life: 4000 });
                }
                if (error.response?.status === 422) {
                    const errors = error.response.data.errors;
                    console.log(errors);
                    for (const field in errors) {
                        this.errors[field] = errors[field][0];
                        this.$toast.add({ severity: 'error', summary: 'Error', detail: errors[field][0], life: 4000 });
                    }
                } else {
                    const errors = error.response.data.details;
                    console.error('Error submitting feedback:', error);
                    this.$toast.add({ severity: 'error', summary: 'Error', detail: errors, life: 4000 });
                }
            }
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

<style scoped>
/* Your existing styles */
</style>

<style>
/* Adding a cursor style to indicate the header is draggable */
.cursor-move {
    cursor: move;
    /* This changes the cursor to a 'move' cursor on hover */
}


::-webkit-scrollbar {
    display: none;
}

.user-page {
    padding: 0px 20px;
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

    #app>div>main>div>div.col-span-1.lg\:col-span-3.border.rounded-xl.shadow-xl>div.pb-2.p-4>div.flex.flex-wrap.justify-between.items-center>div.inline-flex.items-center.rounded-md.bg-primary.px-2.py-1.text-sm.md\:text-xl.font-medium.text-white.ring-1.ring-inset.ring-primary.mb-1,

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

main {
    @apply !py-2
}

.p-card-body {
    @apply !py-0
}
.p-dialog-header {
    @apply !p-0
}
</style>
