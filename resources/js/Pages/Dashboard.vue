<template>

    <Head title="Dashboard" />
    <AuthenticatedLayout>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 p-3">
            <!-- Today's Call-Out Count -->
            <div class="bg-white p-4 rounded-lg shadow-md flex flex-col justify-between">
                <div class="grid grid-cols-4">
                    <div class="grid col-span-3">
                        <h3 class="text-sm font-semibold mb-4">Today's Call-Out Count</h3>
                        <div class="flex items-center">
                            <p class="text-4xl font-bold mr-2">{{ data.todaysCallOutCount }}/100</p>
                        </div>
                    </div>
                    <div class="flex justify-end   ">
                        <svg width="40" height="54" viewBox="0 0 38 38" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M27.4444 16.8889V10.5556H21.1111V6.33333H27.4444V0H31.6667V6.33333H38V10.5556H31.6667V16.8889H27.4444ZM35.7833 38C31.3852 38 27.0398 37.0416 22.7472 35.1247C18.4546 33.2078 14.5491 30.4894 11.0306 26.9694C7.51204 23.4495 4.79433 19.544 2.87744 15.2528C0.960555 10.9616 0.00140741 6.61622 0 2.21667C0 1.58333 0.211111 1.05556 0.633333 0.633333C1.05556 0.211111 1.58333 0 2.21667 0H10.7667C11.2593 0 11.6991 0.167481 12.0861 0.502444C12.4731 0.837407 12.7019 1.23289 12.7722 1.68889L14.1444 9.07778C14.2148 9.64074 14.1972 10.1157 14.0917 10.5028C13.9861 10.8898 13.7926 11.2241 13.5111 11.5056L8.39167 16.6778C9.09537 17.9796 9.93067 19.2371 10.8976 20.4503C11.8644 21.6635 12.9291 22.8338 14.0917 23.9611C15.1824 25.0519 16.3259 26.0638 17.5222 26.9969C18.7185 27.93 19.9852 28.7829 21.3222 29.5556L26.2833 24.5944C26.6 24.2778 27.0138 24.0406 27.5247 23.883C28.0356 23.7254 28.5366 23.681 29.0278 23.75L36.3111 25.2278C36.8037 25.3685 37.2083 25.624 37.525 25.9941C37.8417 26.3643 38 26.7773 38 27.2333V35.7833C38 36.4167 37.7889 36.9444 37.3667 37.3667C36.9444 37.7889 36.4167 38 35.7833 38Z"
                                fill="#77A697" />
                        </svg>
                    </div>
                </div>
                <div class="mt-2">
                    <div class="w-full h-2 bg-[#8EC6B4] rounded-full">
                        <div class="h-2 bg-[#77A697] rounded-full"
                            :style="{ width: `${(data.todaysCallOutCount / 100) * 100}%` }"></div>
                    </div>
                </div>
            </div>

            <!-- Today's Completed Addresses -->
            <div class="bg-white p-4 rounded-lg shadow-md flex flex-col justify-between">
                <div class="grid grid-cols-4">
                    <div class="grid col-span-3">
                        <h3 class="text-sm font-semibold mb-4">Today’s Completed Addresses</h3>
                        <div class="flex items-center">
                            <p class="text-4xl font-bold mr-2">{{ data.todaysCompletedAddresses }}/60</p>
                        </div>
                    </div>
                    <div class="flex justify-end  ">
                        <svg width="40" height="54" viewBox="0 0 44 49" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M29.5436 43.0008L25.3541 38.8113L25.1773 38.6345L25.0005 38.8113L22.6235 41.1883L22.4467 41.3651L22.6235 41.5418L29.3668 48.2851L29.5436 48.4619L29.7203 48.2851L43.2069 34.7986L43.3837 34.6218L43.2069 34.445L40.8299 32.068L40.6531 31.8912L40.4763 32.068L29.5436 43.0008Z"
                                fill="#383838" stroke="#383838" stroke-width="0.5" />
                            <path
                                d="M34.6019 5.71285H34.6011H29.7937L29.7937 4.27703L29.7937 4.27628C29.7908 3.31685 29.4084 2.39752 28.73 1.7191C28.0515 1.04067 27.1322 0.658262 26.1728 0.655396H26.172L12.6855 0.655394L12.6847 0.655397C11.7253 0.658262 10.806 1.04067 10.1276 1.7191C9.44912 2.39752 9.06672 3.31685 9.06385 4.27628V4.27703V5.71285L4.2564 5.71285L4.25565 5.71285C3.29622 5.71571 2.37689 6.09812 1.69847 6.77655C1.02004 7.45498 0.637632 8.3743 0.634766 9.33374V9.33448L0.634765 44.7366L0.634767 44.7374C0.637632 45.6968 1.02004 46.6162 1.69847 47.2946C2.37689 47.973 3.29622 48.3554 4.25565 48.3583H4.2564H19.4288H19.6788V48.1083V44.7366V44.4866H19.4288H4.5064V9.58448H9.06385V14.3919V14.6419H9.31385H29.5437H29.7937V14.3919V9.58448H34.3511V27.8785V28.1285H34.6011H37.9727H38.2227V27.8785V9.33448V9.33374C38.2199 8.3743 37.8375 7.45498 37.159 6.77655C36.4806 6.09812 35.5613 5.71571 34.6019 5.71285ZM25.922 10.7703H12.9355V4.52703H25.922V10.7703Z"
                                fill="#383838" stroke="#383838" stroke-width="0.5" />
                        </svg>
                    </div>
                </div>
                <div class="mt-2">
                    <div class="w-full h-2 bg-[#A6A2A0] rounded-full">
                        <div class="h-2 bg-[#383838] rounded-full"
                            :style="{ width: `${(data.todaysCompletedAddresses / 100) * 100}%` }"></div>
                    </div>
                </div>
            </div>

            <!-- Yesterday's Break Times -->
            <div class="bg-white p-4 rounded-lg shadow-md flex flex-col justify-between">
                <div class="grid grid-cols-4">
                    <div class="grid col-span-3">
                        <h3 class="text-sm font-semibold mb-4">Yesterday’s Break Times</h3>
                        <div class="flex items-center">
                            <p class="text-4xl font-bold mr-2">{{ formatTime(data.todaysBreakTime) }}</p>
                        </div>
                    </div>
                    <div class="xl:flex justify-end  hidden">
                        <svg width="40" height="54" viewBox="0 0 40 54" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M2.5 17H37.5" stroke="#945F3A" stroke-width="4" stroke-linecap="round" />
                            <path
                                d="M7.69146 19.6787C7.66691 19.3363 7.71317 18.9924 7.82738 18.6685C7.94158 18.3447 8.12127 18.0479 8.35524 17.7965C8.58921 17.5452 8.87244 17.3448 9.18729 17.2077C9.50213 17.0707 9.84183 17 10.1852 17H29.8152C30.1585 17 30.4981 17.0707 30.8128 17.2076C31.1276 17.3446 31.4107 17.5449 31.6447 17.7961C31.8786 18.0473 32.0584 18.344 32.1726 18.6677C32.2869 18.9914 32.3333 19.3351 32.309 19.6775L30.1665 49.6775C30.1216 50.3082 29.8393 50.8985 29.3766 51.3295C28.9139 51.7604 28.305 52 27.6727 52H12.3277C11.6954 52 11.0865 51.7604 10.6238 51.3295C10.1611 50.8985 9.87885 50.3082 9.83396 49.6775L7.69146 19.6787Z"
                                stroke="#945F3A" stroke-width="4" />
                            <path
                                d="M20 9.5V4.5C20 3.83696 20.2634 3.20107 20.7322 2.73223C21.2011 2.26339 21.837 2 22.5 2H26.25"
                                stroke="#945F3A" stroke-width="4" stroke-linecap="round" />
                            <path
                                d="M8.4025 11.5887C8.49982 11.0052 8.80091 10.4751 9.25224 10.0927C9.70358 9.71023 10.2759 9.50022 10.8675 9.5H29.1325C29.7243 9.49993 30.2969 9.7098 30.7485 10.0923C31.2001 10.4747 31.5014 11.005 31.5987 11.5887L32.5 17H7.5L8.4025 11.5887Z"
                                stroke="#945F3A" stroke-width="4" />
                        </svg>
                    </div>
                </div>

                <div class="">
                    <div class="w-full h-2 bg-[#E4A57B] rounded-full">
                        <div class="h-2 bg-[#945F3A] rounded-full" style="width: 70%"></div>
                    </div>
                </div>
            </div>

            <!-- Additional Content for Mobile Responsiveness -->
            <div class="grid grid-cols-1 ">
                <!-- Login Time -->
                <div class="grid grid-cols-3 border mb-1  rounded-md p-4 shadow ">
                    <div class="col-span-2">
                        <p class="font-sans font-semibold text-sm">Login Time</p>
                        <h1 class="font-sans font-extrabold text-2xl">{{ new
                                Date($page.props.auth.logintime).toLocaleTimeString('en-US', {
                                    hour: '2-digit', minute:
                                        '2-digit', hour12: true
                                }) }}</h1>
                    </div>
                    <div class="col-span-1 text-end">
                        <span class="pi pi-clock  !text-[3rem] text-[#77A697] ml-2" data-pc-section="icon"></span>
                    </div>
                </div>

                <!-- Yesterday’s Working Hours -->
                <div class="grid grid-cols-3 border mt-2  rounded-md p-4 shadow ">
                    <div class="col-span-2">
                        <p class="font-sans font-semibold text-sm">Yesterday’s Working Hours</p>
                        <h1 class="font-sans font-extrabold text-2xl">{{ formatTime(data.yesterdayWorkingHours) }}</h1>
                    </div>
                    <div class="col-span-1 text-end">
                        <span class="pi pi-clock !text-[3rem] text-[#818FA0] ml-2" data-pc-section="icon"></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-5 gap-6 p-3 ">
            <!-- Notifications -->
            <div class="bg-white p-4 rounded-lg shadow-md xl:flex justify-between hidden md:col-span-2">
                <div class="flex flex-col justify-between w-full">
                    <div >
                        <h3 class="text-sm font-semibold mb-4">Notifications</h3>
                        <p class="text-gray-600 mb-6">
                            Alerts for follow-up, assign projects or system updates. Only show if notifications are
                            available.
                        </p>
                    </div>
                    <div class="my-auto">
                        <!-- Moved the Read button here -->
                        <button
                            class="bg-[#C88352] hover:bg-orange-600 text-white text-xl px-[5rem] h-[49px] py-2 rounded">Read</button>
                    </div>
                </div>
                <div class="flex flex-col justify-end w-full items-end">
                    <div class="flex items-center space-x-2">
                        <button type="button"
                            class="p-button !bg-secondary !border-secondary p-component p-button-icon-only">
                            <svg width="16" height="22" viewBox="0 0 16 22" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M8 16V21M5 9.76C4.9998 10.1321 4.89581 10.4967 4.69972 10.813C4.50363 11.1292 4.22321 11.3844 3.89 11.55L2.11 12.45C1.77679 12.6156 1.49637 12.8708 1.30028 13.187C1.10419 13.5033 1.0002 13.8679 1 14.24V15C1 15.2652 1.10536 15.5196 1.29289 15.7071C1.48043 15.8946 1.73478 16 2 16H14C14.2652 16 14.5196 15.8946 14.7071 15.7071C14.8946 15.5196 15 15.2652 15 15V14.24C14.9998 13.8679 14.8958 13.5033 14.6997 13.187C14.5036 12.8708 14.2232 12.6156 13.89 12.45L12.11 11.55C11.7768 11.3844 11.4964 11.1292 11.3003 10.813C11.1042 10.4967 11.0002 10.1321 11 9.76V6C11 5.73478 11.1054 5.48043 11.2929 5.29289C11.4804 5.10536 11.7348 5 12 5C12.5304 5 13.0391 4.78929 13.4142 4.41421C13.7893 4.03914 14 3.53043 14 3C14 2.46957 13.7893 1.96086 13.4142 1.58579C13.0391 1.21071 12.5304 1 12 1H4C3.46957 1 2.96086 1.21071 2.58579 1.58579C2.21071 1.96086 2 2.46957 2 3C2 3.53043 2.21071 4.03914 2.58579 4.41421C2.96086 4.78929 3.46957 5 4 5C4.26522 5 4.51957 5.10536 4.70711 5.29289C4.89464 5.48043 5 5.73478 5 6V9.76Z"
                                    stroke="white" stroke-width="1.33333" stroke-linecap="round"
                                    stroke-linejoin="round" />
                            </svg>
                        </button>
                        <Button severity="contrast" icon="pi pi-heart" class="" />
                        <Button icon="pi pi-trash" class="" />
                    </div>
                    <div class="my-auto flex justify-center">
                        <!-- Moved the SVG here -->
                        <svg width="105" height="111" viewBox="0 0 105 111" fill="none"
                            xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M103.802 88.9738C100.404 86.0382 97.4296 82.6729 94.9581 78.9683C92.2598 73.8547 90.6426 68.2704 90.2013 62.5429V45.6736C90.2247 36.6775 86.8576 27.9829 80.7328 21.2232C74.608 14.4635 66.147 10.1041 56.9396 8.96397V4.55882C56.9396 3.34974 56.444 2.19019 55.5619 1.33525C54.6797 0.480303 53.4833 0 52.2357 0C50.9882 0 49.7918 0.480303 48.9096 1.33525C48.0275 2.19019 47.5319 3.34974 47.5319 4.55882V9.03227C38.4069 10.2545 30.0482 14.6403 24.0039 21.3773C17.9595 28.1143 14.6391 36.7459 14.6577 45.6736V62.5429C14.2164 68.2704 12.5992 73.8547 9.90101 78.9683C7.47291 82.6644 4.54599 86.0293 1.19799 88.9738C0.822141 89.2938 0.520918 89.6877 0.314357 90.1293C0.107796 90.5709 0.00063351 91.0501 0 91.5349V96.1791C0 97.0848 0.371224 97.9534 1.03201 98.5938C1.69279 99.2342 2.589 99.594 3.52349 99.594H101.477C102.411 99.594 103.307 99.2342 103.968 98.5938C104.629 97.9534 105 97.0848 105 96.1791V91.5349C104.999 91.0501 104.892 90.5709 104.686 90.1293C104.479 89.6877 104.178 89.2938 103.802 88.9738ZM7.32886 92.7643C10.6071 89.695 13.4936 86.2556 15.9262 82.5198C19.325 76.3439 21.3081 69.5298 21.7399 62.5429V45.6736C21.6002 41.6715 22.2929 37.6831 23.7767 33.9458C25.2605 30.2086 27.5051 26.799 30.3769 23.92C33.2486 21.041 36.6888 18.7515 40.4926 17.1879C44.2963 15.6242 48.3858 14.8184 52.5176 14.8184C56.6494 14.8184 60.7389 15.6242 64.5426 17.1879C68.3464 18.7515 71.7866 21.041 74.6584 23.92C77.5301 26.799 79.7747 30.2086 81.2586 33.9458C82.7424 37.6831 83.435 41.6715 83.2953 45.6736V62.5429C83.7272 69.5298 85.7103 76.3439 89.1091 82.5198C91.5417 86.2556 94.4281 89.695 97.7064 92.7643H7.32886Z"
                                fill="#77A697" />
                            <path
                                d="M52.6745 110.999C54.8941 110.95 57.0241 110.141 58.6877 108.716C60.3513 107.291 61.4414 105.342 61.7651 103.213H43.2316C43.5644 105.4 44.7055 107.395 46.4425 108.827C48.1795 110.259 50.3942 111.031 52.6745 110.999Z"
                                fill="#77A697" />
                        </svg>
                    </div>
                </div>
            </div>


            <!-- Today's Success Call-Out Rate -->
            <div class="bg-white p-2 rounded-lg shadow flex xl:col-span-1 col-span-2 items-center justify-center w-full xl:h-full h-[14rem]">
                <div class="flex flex-col items-center w-full h-full">
                    <h4 class="text-gray-600 mb-2 font-semibold text-center">Today`s Success Call-Out Rate</h4>
                    <div class="circular-progress-bar relative flex items-center justify-center w-full h-full">
                        <svg class="progress-circle absolute inset-0 w-full h-full" viewBox="0 0 120 120"
                            preserveAspectRatio="xMidYMid meet">
                            <circle class="progress-circle-bg" cx="60" cy="60" r="54" fill="none" stroke="#e5e7eb"
                                stroke-width="10" />
                            <circle ref="progressCircleFill" class="progress-circle-fill" cx="60" cy="60" r="54"
                                fill="none" stroke="#3b82f6" stroke-width="10" stroke-dasharray="339.292"
                                stroke-dashoffset="190" />
                        </svg>
                        <span class="progress-text absolute text-gray-800 font-semibold text-xl">{{
                            Math.round(data.successRate) }}%</span>
                    </div>
                </div>
            </div>

            <div class="col-span-2">
                <div class="bg-white shadow-lg rounded-lg mb-5 p-4 border border-green-200">
                    <div class="grid grid-cols-2 items-center justify-between">
                        <div>
                            <h2 class=" font-extrabold text-sm ">Today's Call-Out Volume</h2>
                            <div class="grid items-center mt-2">
                                <span class="text-4xl font-semibold text-gray-800">{{ data.todaysCallOutCount }}</span>
                            </div>
                        </div>
                        <div>
                            <Chart type="line" :height="90" :data="callVolumeGraphData" :options="chartOptions" />
                        </div>
                    </div>
                </div>
                <div class="bg-white shadow-lg rounded-lg mt-1 p-4 border border-green-200">
                    <div class="grid grid-cols-2 items-center justify-between">
                        <div>
                            <h2 class=" text-sm font-extrabold">Average weekly address processing time</h2>
                            <div class="grid items-center mt-2">
                                <span class="text-4xl font-semibold text-gray-800">{{
                                formatTime(data.averageProcessingTime) }}</span>
                            </div>
                        </div>
                        <div>
                            <Chart type="line" :height="90" :data="secchartData" :options="secchartOptions" />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Buttons at the bottom -->
        <div class="flex justify-center flex-wrap my-4">
            <Link :href="route('dash')" class="bg-primary text-white flex px-[5rem] py-3 text-xl mx-2 rounded mb-2">
            <span class="mr-1">
                <svg width="33" height="33" viewBox="0 0 33 33" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path
                        d="M21.1876 4.3261L22.2749 4.61979C23.6045 4.97951 24.8166 5.68146 25.7902 6.65563C26.7638 7.62981 27.4649 8.84216 27.8236 10.1718L28.1163 11.2576L25.944 11.8439L25.6503 10.758C25.3939 9.80828 24.893 8.94233 24.1975 8.24655C23.5019 7.55076 22.636 7.04946 21.6862 6.79265L20.6001 6.49783L21.1876 4.3261ZM4.24637 4.56578H13.9033L15.5769 12.0937L13.4835 14.1867C14.7144 16.1019 16.3431 17.7298 18.259 18.96L20.3525 16.8681L27.8822 18.5414V28.196H26.7567C22.4238 28.2034 18.1818 26.9542 14.5448 24.5997C11.863 22.8645 9.5791 20.5812 7.84351 17.9C5.48851 14.2639 4.23898 10.0229 4.24637 5.69103V4.56578ZM6.52779 6.81628C6.71745 10.327 7.82295 13.7269 9.73438 16.678C11.297 19.0916 13.353 21.1471 15.7671 22.7093C18.7189 24.6203 22.1196 25.7255 25.6311 25.9151V20.3463L21.0694 19.3335L18.6226 21.781L17.8763 21.3567C15.043 19.747 12.6973 17.4019 11.0872 14.5692L10.6629 13.8232L13.1109 11.3769L12.098 6.81628H6.52779ZM20.1589 8.12719L21.2461 8.42088C21.911 8.60074 22.517 8.95172 23.0038 9.4388C23.4906 9.92589 23.8412 10.5321 24.0205 11.1969L24.3132 12.2827L22.1409 12.869L21.8472 11.7831C21.7702 11.4982 21.6198 11.2384 21.4111 11.0297C21.2023 10.821 20.9425 10.6707 20.6575 10.5937L19.5714 10.3L20.1589 8.12719Z"
                        fill="white" />
                </svg>
            </span> Start Call</Link>

            <!-- <Link :href="route('dash')" class="bg-[#383838] text-white flex px-[4rem] py-3 text-xl mx-2 rounded mb-2">
            <span class="mr-1">
                <svg width="32" height="33" viewBox="0 0 32 33" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M4.03394 3.6617V20.2422H27.9064V3.6617" stroke="white" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round" />
                    <path d="M19.949 24.8817L15.9702 28.8604L11.9915 24.8817" stroke="white" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round" />
                    <path d="M15.9702 20.2398V28.8604" stroke="white" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round" />
                    <path d="M18.6152 7.62619L21.9384 10.9561L18.6152 14.3315" stroke="white" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round" />
                    <path d="M10.0027 10.9567H21.9383" stroke="white" stroke-width="2" stroke-linecap="round" />
                </svg>
            </span> Follow-Ups</Link> -->

            <Link :href="route('statistics.index')"
                class="bg-secondary text-white flex px-[5rem] py-3 text-xl mx-2 rounded mb-2">
            <span class="mr-1">
                <svg width="32" height="33" viewBox="0 0 32 33" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path
                        d="M26.3802 24.8419C26.9637 23.9135 27.3086 22.8393 27.3086 21.6722C27.3086 18.3698 24.6428 15.704 21.3404 15.704C18.0381 15.704 15.3723 18.3698 15.3723 21.6722C15.3723 24.9745 18.0381 27.6403 21.3272 27.6403C22.4943 27.6403 23.5818 27.2955 24.4969 26.7119L28.6348 30.8498L30.5181 28.9665L26.3802 24.8419ZM21.3404 24.9878C20.4611 24.9878 19.6177 24.6385 18.9959 24.0167C18.3741 23.3949 18.0248 22.5515 18.0248 21.6722C18.0248 20.7928 18.3741 19.9495 18.9959 19.3277C19.6177 18.7059 20.4611 18.3565 21.3404 18.3565C22.2198 18.3565 23.0631 18.7059 23.6849 19.3277C24.3067 19.9495 24.6561 20.7928 24.6561 21.6722C24.6561 22.5515 24.3067 23.3949 23.6849 24.0167C23.0631 24.6385 22.2198 24.9878 21.3404 24.9878ZM20.863 13.7147C19.8816 13.7412 18.9399 13.9534 18.0779 14.3115L17.3484 13.2107L12.3087 21.4069L8.31665 16.7385L3.50236 24.444L1.34058 22.8923L7.97183 12.2823L11.9506 16.9242L17.2556 8.30357L20.863 13.7147ZM24.298 14.3778C23.4492 14.0064 22.5341 13.781 21.5792 13.7279L28.3696 2.99857L30.5181 4.56355L24.298 14.3778Z"
                        fill="white" />
                </svg>
            </span> Statistics</Link>
        </div>
    </AuthenticatedLayout>
</template>



<script>


export default {
    props: {
        data: Object, // Ensure to receive the data from backend
    },
    data() {
        return {

            chartData: {
                labels: Array(10).fill(''), // Dummy data labels
                datasets: [
                    {
                        data: [1, 3, 2, 5, 3, 6, 4, 7, 5, 4], // Dummy data
                        fill: true,
                        borderColor: '#795548',
                        backgroundColor: 'rgba(121, 85, 72, 0.3)',
                        tension: 0.3, // Smoothness of line
                        pointRadius: 1,
                        pointHitRadius: 70,
                    },
                ],
            },
            chartOptions: {
                plugins: {
                    legend: {
                        display: false,
                    },
                },
                scales: {
                    x: {
                        display: false,
                    },
                    y: {
                        display: false,
                    },
                },
            },
            secchartData: {
                labels: Object.keys(this.data.processingTimeGraphData), // Dummy data labels
                datasets: [
                    {
                        data: Object.values(this.data.processingTimeGraphData), // Dummy data
                        fill: true,
                        borderColor: '#77A697',
                        backgroundColor: '#E2FFFF',
                        tension: 0.3, // Smoothness of line
                        pointRadius: 1,
                        pointHitRadius: 70,
                    },
                ],
            },
            secchartOptions: {
                plugins: {
                    // tooltip: {
                    //     callbacks: {
                    //         label: function (tooltipItem, data) {
                    //             let seconds = tooltipItem.yLabel;
                    //             seconds = parseInt(seconds, 10);
                    //             if (isNaN(seconds) || seconds === null || seconds === undefined) {
                    //                 return seconds; // Return default value if seconds is invalid
                    //             }
                    //             let hours = Math.floor(seconds / 3600);
                    //             let minutes = Math.floor((seconds % 3600) / 60);
                    //             seconds = seconds % 60;

                    //             // Format to two digits for each unit
                    //             let formattedTime =
                    //                 ('0' + hours).slice(-2) + ':' +
                    //                 ('0' + minutes).slice(-2) + ':' +
                    //                 ('0' + seconds).slice(-2);

                    //             return seconds;
                    //         },
                    //     },
                    // },
                    legend: {
                        display: false,
                    },
                },
                scales: {
                    x: {
                        display: false,
                    },
                    y: {
                        display: false,
                    },
                },
            },
        };
    },
    computed: {

        callVolumeGraphData() {
            // Preparing data for call volume graph
            return {
                labels: Object.keys(this.data.callVolumeGraphData),
                datasets: [
                    {
                        data: Object.values(this.data.callVolumeGraphData),
                        fill: true,
                        borderColor: '#795548',
                        backgroundColor: 'rgba(121, 85, 72, 0.3)',
                        tension: 0.3,
                        pointRadius: 1,
                        pointHitRadius: 70,
                    },
                ],
            };
        },
        breakTimeGraphData() {
            // Preparing data for break time graph
            return {
                labels: Object.keys(this.data.breakTimeGraphData),
                datasets: [
                    {
                        data: Object.values(this.data.breakTimeGraphData),
                        fill: true,
                        borderColor: '#77A697',
                        backgroundColor: '#E2FFFF',
                        tension: 0.3,
                        pointRadius: 1,
                        pointHitRadius: 70,
                    },
                ],
            };
        },
        strokeDashoffset() {
            const progressValue = 44;
            const circleLength = 314;
            return circleLength - (progressValue / 100) * circleLength;
        }
    },
    methods: {
        padZero(value) {
            return (value < 10 ? '0' : '') + value;
        },
        formatTime(seconds) {
            // Ensure seconds is an integer
            seconds = Math.floor(seconds);

            const hours = Math.floor(seconds / 3600);
            const minutes = Math.floor((seconds % 3600) / 60);
            const secs = seconds % 60;

            return `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
        },

    },

    mounted() {
        const radius = 54;
        const circumference = 2 * Math.PI * radius;

        // Update the offset calculation to use the successRate from the data
        const progress = this.data.successRate;
        const offset = circumference - (progress / 100) * circumference;

        this.$refs.progressCircleFill.style.strokeDasharray = `${circumference}`;
        this.$refs.progressCircleFill.style.strokeDashoffset = `${offset}`;


        console.log(this.data);
    },
};
</script>
<style scoped>
.circular-progress-bar {
    position: relative;
    width: 100% !important;
    height: 100% !important;
}

.progress-circle-bg {
    stroke: #A7704A;
    stroke-width: 10;
}

.progress-circle {
    width: 100%;
    height: 100%;
}

.progress-text {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    font-size: 30px;
    font-weight: bold;
}

.progress-circle-fill {
    stroke: #E4A57B;
    stroke-width: 10;
    stroke-dasharray: 314;
    stroke-dashoffset: 314;
    stroke-linecap: round;
    transition: stroke-dashoffset 0.5s ease-in-out;
}

#app>div>main>div.grid.grid-cols-1.xl\:grid-cols-5.gap-6.p-6>div.col-span-2>div.bg-white.shadow-lg.rounded-lg.mb-1.p-4.border.border-green-200>div>div:nth-child(2)>div>canvas,
#app>div>main>div.grid.grid-cols-1.xl\:grid-cols-5.gap-6.p-6>div.col-span-2>div.bg-white.shadow-lg.rounded-lg.mt-1.p-4.border.border-green-200>div>div:nth-child(2)>div>canvas {
    width: 100% !important;
}

#app>div {
    background: #eae9e8;
}

#app>div>main>div.grid.grid-cols-1.md\:grid-cols-2.lg\:grid-cols-4.gap-6.p-6>div.grid.grid-cols-1>div {
    background: #fff;
}

#app>div>main>div.grid.grid-cols-1.md\:grid-cols-2.lg\:grid-cols-4.gap-6.p-6>div.grid.grid-cols-1>div.grid.grid-cols-3.border.mb-1.rounded-md.p-4.shadow {
    margin-bottom: 20px;
}

#app>div>main>div.grid.grid-cols-1.xl\:grid-cols-5.gap-6.p-6>div.col-span-2>div.bg-white.shadow-lg.rounded-lg.mb-1.p-4.border.border-green-200 {
    margin-bottom: 25px;
}

@media only screen and (min-width:768px) {
    #app>div {
        height: calc(100vh - 100px);
    }

    #app>div>div {
        height: calc(100vh - 150px);
        overflow: hidden;
    }

    #app>div>main {
        overflow-y: auto;
    }

    #app>div>div>aside>nav {
        height: calc(100vh - 180px);
    }
}

@media only screen and (max-width:768px) {
    #app>div>div>aside {
        padding: 14px 10px;
    }

    #app>div>main>div.flex.justify-center.flex-wrap.my-4 a {
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    #app>div>main>div.flex.justify-center.flex-wrap.my-4 {
        padding: 15px;
    }

    #app>div>main>div.grid.grid-cols-1.xl\:grid-cols-5.gap-6.p-6>div.bg-white.p-2.rounded-lg.shadow.flex.items-center.justify-center.w-full.xl\:h-full.h-\[14rem\] {
        grid-column: span 2 / span 2;
    }
}
</style>
