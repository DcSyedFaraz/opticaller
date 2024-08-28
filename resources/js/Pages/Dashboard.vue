<template>

    <Head title="Users" />
    <AuthenticatedLayout>
        <div class="grid grid-cols-4 gap-6 p-6 ">
            <!-- Today's Call-Out Count -->
            <div class="bg-white p-4 rounded-lg shadow-md flex flex-col justify-between">
                <div class="grid grid-cols-4">
                    <div class="grid col-span-3">
                        <h3 class="text-sm font-semibold mb-4">Today's Call-Out Count</h3>
                        <div class="flex items-center">
                            <p class="text-4xl font-bold  mr-2">{{ data.todaysCallOutCount }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Today's Completed Addresses -->
            <div class="bg-white p-4 rounded-lg shadow-md flex flex-col justify-between">
                <div class="grid grid-cols-4">
                    <div class="grid col-span-3">
                        <h3 class="text-sm font-semibold mb-4">Today’s Completed Addresses</h3>
                        <div class="flex items-center">
                            <p class="text-4xl font-bold  mr-2">{{ data.todaysCompletedAddresses }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Yesterday's Break Times -->
            <div class="bg-white p-4 rounded-lg shadow-md flex flex-col justify-between">
                <div class="grid grid-cols-4">
                    <div class="grid col-span-3">
                        <h3 class="text-sm font-semibold mb-4">Yesturday’s Break Times</h3>
                        <div class="flex items-center">
                            <p class="text-4xl font-bold  mr-2">{{ formatTime(data.todaysBreakTime) }}</p>
                        </div>


                    </div>
                    <div class="grid mt-3">
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

                <div class="mt-2">
                    <div class="my-2 flex items-center">
                        <i class="pi pi-angle-up text-[#945F3A] mr-1"></i>
                        <p class="text-sm text-[#945F3A]">43.2 than last month</p>
                    </div>
                    <div class="w-full h-2 bg-[#E4A57B] rounded-full">
                        <div class="h-2 bg-[#945F3A] rounded-full" style="width: 70%"></div>
                    </div>
                </div>
            </div>
            <div class="grid grid-cols-1">

                <!-- Login Time -->
                <div class="grid grid-cols-3 border mb-1 h-[5rem] rounded-md p-4 shadow shadow-secondary">
                    <div class="col-span-2">
                        <p class=" font-sans text-sm">Login Time</p>
                        <h1 class=" font-sans font-extrabold text-2xl">{{ new
                                Date($page.props.auth.logintime).toLocaleTimeString('en-US', {
                                    hour: '2-digit', minute:
                                        '2-digit', hour12: true
                                }) }}</h1>
                    </div>
                    <div class="col-span-1">
                        <span class=" pi pi-clock !text-[3rem] text-[#77A697] ml-2" data-pc-section="icon"></span>
                    </div>
                </div>
                <!-- Login Time -->
                <div class="grid grid-cols-3 border mt-1  h-[5rem] rounded-md p-4 shadow shadow-secondary">
                    <div class="col-span-2">
                        <p class=" font-sans text-sm">Yesturday’s Working Hours</p>
                        <h1 class=" font-sans font-extrabold text-2xl">{{ new
                                Date($page.props.auth.logintime).toLocaleTimeString('en-US', {
                                    hour: '2-digit', minute:
                                        '2-digit', hour12: true
                                }) }}</h1>
                    </div>
                    <div class="col-span-1">
                        <span class=" pi pi-clock !text-[3rem] text-[#818FA0] ml-2" data-pc-section="icon"></span>
                    </div>
                </div>

            </div>

        </div>

        <div class="grid grid-cols-5 gap-6 p-6 ">

            <!-- Notifications -->
            <div class="bg-white p-4 rounded-lg shadow-md flex  justify-between col-span-2">
                <div class="">
                    <h3 class="text-lg font-semibold mb-4">Notifications</h3>
                    <p class="text-gray-600 mb-6">
                        Alerts for follow-up, assign projects or system updates. Only show if notifications are
                        available.
                    </p>
                    <button
                        class="bg-[#C88352] hover:bg-orange-600 text-white text-xl px-4 w-[249px] h-[59px] py-2 rounded-lg">Read</button>

                </div>
                <div class="">
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

                        <!-- PrimeVue Heart Button -->
                        <Button severity="contrast" icon="pi pi-heart" class="" />

                        <!-- PrimeVue Trash Button -->
                        <Button icon="pi pi-trash" class="" />

                    </div>

                    <div class="mt-8 flex justify-center">
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
            <div class="bg-white p-2 rounded-lg shadow flex items-center justify-center w-full h-full">
                <div class="flex flex-col items-center w-full h-full">
                    <h4 class="text-gray-600 mb-2 text-center">Success Call-Out Rate</h4>
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
                                data.successRate.toFixed(2) }}%</span>
                    </div>
                </div>
            </div>
            <div class="col-span-2">

                <div class="bg-white shadow-lg rounded-lg mb-1 p-4 border border-green-200 ">
                    <div class="grid grid-cols-2 items-center justify-between ">
                        <div>
                            <h2 class="text-gray-600 text-sm font-medium">Today's Call-Out Volume</h2>
                            <div class="grid items-center mt-2">
                                <span class="text-4xl font-semibold text-gray-800">6,29</span>
                                <div class="flex items-center mt-1 text-sm text-red-500">
                                    <i class="pi pi-arrow-down text-red-500 mr-1"></i>
                                    <span>-2.4%</span> <span class="ml-2 text-bold text-[#818FA0]">than yesterday</span>
                                </div>
                            </div>
                        </div>
                        <div>
                            <Chart type="line" :data="callVolumeGraphData" :options="chartOptions" />

                        </div>
                    </div>
                </div>
                <div class="bg-white shadow-lg rounded-lg mt-1 p-4 border border-green-200 ">
                    <div class="grid grid-cols-2 items-center justify-between ">
                        <div>
                            <h2 class="text-gray-600 text-sm font-medium">Average weekly address processing time</h2>
                            <div class="grid items-center mt-2">
                                <span class="text-4xl font-semibold text-gray-800">{{ data.averageProcessingTime }}</span>
                                <div class="flex items-center mt-1 text-sm text-red-500">
                                    <i class="pi pi-arrow-down text-red-500 mr-1"></i>
                                    <span>-2.4%</span> <span class="ml-2 text-bold text-[#818FA0]">than yesterday</span>
                                </div>
                            </div>
                        </div>
                        <div>
                            <Chart type="line" :height="130" :data="secchartData" :options="secchartOptions" />

                        </div>
                    </div>
                </div>

            </div>
        </div>


        <!-- Buttons at the bottom -->
        <div class="flex justify-center my-4">
            <button class="bg-primary  text-white flex px-[5rem] py-3 text-xl mx-2 rounded-lg"><span class="mr-1">
                    <svg width="33" height="33" viewBox="0 0 33 33" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M21.1876 4.3261L22.2749 4.61979C23.6045 4.97951 24.8166 5.68146 25.7902 6.65563C26.7638 7.62981 27.4649 8.84216 27.8236 10.1718L28.1163 11.2576L25.944 11.8439L25.6503 10.758C25.3939 9.80828 24.893 8.94233 24.1975 8.24655C23.5019 7.55076 22.636 7.04946 21.6862 6.79265L20.6001 6.49783L21.1876 4.3261ZM4.24637 4.56578H13.9033L15.5769 12.0937L13.4835 14.1867C14.7144 16.1019 16.3431 17.7298 18.259 18.96L20.3525 16.8681L27.8822 18.5414V28.196H26.7567C22.4238 28.2034 18.1818 26.9542 14.5448 24.5997C11.863 22.8645 9.5791 20.5812 7.84351 17.9C5.48851 14.2639 4.23898 10.0229 4.24637 5.69103V4.56578ZM6.52779 6.81628C6.71745 10.327 7.82295 13.7269 9.73438 16.678C11.297 19.0916 13.353 21.1471 15.7671 22.7093C18.7189 24.6203 22.1196 25.7255 25.6311 25.9151V20.3463L21.0694 19.3335L18.6226 21.781L17.8763 21.3567C15.043 19.747 12.6973 17.4019 11.0872 14.5692L10.6629 13.8232L13.1109 11.3769L12.098 6.81628H6.52779ZM20.1589 8.12719L21.2461 8.42088C21.911 8.60074 22.517 8.95172 23.0038 9.4388C23.4906 9.92589 23.8412 10.5321 24.0205 11.1969L24.3132 12.2827L22.1409 12.869L21.8472 11.7831C21.7702 11.4982 21.6198 11.2384 21.4111 11.0297C21.2023 10.821 20.9425 10.6707 20.6575 10.5937L19.5714 10.3L20.1589 8.12719Z"
                            fill="white" />
                    </svg>

                </span> Start Call</button>
            <button class="bg-[#383838]  text-white flex px-[5rem] py-3 text-xl mx-2 rounded-lg"><span class="mr-1">
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

                </span> Follow-Ups</button>
            <button class="bg-secondary  text-white flex px-[5rem] py-3 text-xl mx-2 rounded-lg"><span class="mr-1">
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

                </span> Statistics</button>
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
                labels: Array(10).fill(''), // Dummy data labels
                datasets: [
                    {
                        data: [1, 3, 2, 5, 3, 6, 4, 7, 5, 4], // Dummy data
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
        formatTime(seconds) {
            // Helper method to format seconds into hh:mm:ss
            const hours = Math.floor(seconds / 3600);
            const minutes = Math.floor((seconds % 3600) / 60);
            const secs = seconds % 60;
            return `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
        },
    },

    mounted() {
        const progress = 44;
        const radius = 54;
        const circumference = 2 * Math.PI * radius;
        const offset = circumference - (progress / 100) * circumference;

        this.$refs.progressCircleFill.style.strokeDasharray = `${circumference}`;
        this.$refs.progressCircleFill.style.strokeDashoffset = `${offset}`;
        this.$nextTick(() => {
            const progressCircleFill = this.$refs.progressCircleFill
            progressCircleFill.style.strokeDashoffset = this.strokeDashoffset
        });


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
</style>
