<template>

    <Head title="Performance Metrics" />
    <AuthenticatedLayout>

        <div class="p-6 bg-gray-100 min-h-screen">
            <!-- Top Section -->
            <div class="grid grid-cols-1 xl:grid-cols-5 gap-4">
                <div class="grid col-span-1 xl:col-span-2">
                    <div class="flex flex-col my-2 space-x-4 shadow-secondary bg-white p-2 rounded-lg shadow">
                        <div class="border-b-2 p-1">
                            <span class="mx-4 font-extrabold">
                                Select Date Range
                            </span>
                        </div>
                        <div class="m-2 grid grid-cols-1 md:grid-cols-2 gap-2">
                            <div class="grid grid-cols-1">
                                <DatePicker v-model="startDate" @date-select="onDateChange" iconPos="left" showIcon
                                    fluid placeholder="MM/DD/YY" class="w-full" />
                            </div>
                            <div class="grid grid-cols-1">
                                <DatePicker v-model="endDate" @date-select="onDateChange" iconPos="left" showIcon fluid
                                    placeholder="MM/DD/YY" class="w-full" />
                            </div>
                        </div>
                    </div>

                    <!-- Top Performer Card -->
                    <div
                        class="bg-primary text-white p-4 rounded-lg shadow flex items-center justify-center flex-col mt-4">
                        <div class="grid grid-cols-1 md:grid-cols-2">
                            <div class="grid grid-cols-1 text-center">
                                <h2 class="text-xl font-bold">Top Performer</h2>
                                <p class="text-2xl font-bold">{{ this.data.employeeLeaderboard }}</p>
                                <p class="text-xs text-center mt-2">
                                    Ranking of employees based on performance metrics like calls completed, customer
                                    interest rate,
                                    etc.
                                </p>
                            </div>
                            <div class="grid grid-cols-1 mt-4 md:mt-0">
                                <img src="images/award.png" alt="">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid col-span-1 xl:col-span-2 w-full overflow-x-auto">
                    <div class="bg-white p-4 rounded-lg shadow mt-4 xl:mt-0">
                        <div class="border-b-2 p-1">
                            <span class="mx-4 font-extrabold">
                                Daily Call-Out Volume
                            </span>
                        </div>
                        <Chart type="line" class="mt-5 w-full h-[235px]" :options="chartOptions" :data="chartData" />
                    </div>
                </div>
                <div class="grid lg:grid-cols-2 xl:grid-cols-1">
                    <div class="grid grid-cols-1 sm:grid-cols-3 border  mb-3 rounded-md p-4 shadow shadow-secondary">
                        <div class="col-span-2">
                            <p class="text-[#424E79] font-sans text-sm">Login Time</p>
                            <h1 class="text-[#424E79] font-sans font-extrabold text-xl">
                                {{ new Date($page.props.auth.logintime).toLocaleTimeString('en-US', {
                                    hour: '2-digit',
                                    minute: '2-digit', hour12: true
                                }) }}
                            </h1>
                        </div>
                        <div class="col-span-1 flex justify-end">
                            <span class="pi pi-clock !text-[3rem] text-primary ml-2" data-pc-section="icon"></span>
                        </div>
                    </div>

                    <!-- Success Call-Out Rate -->
                    <div class="bg-white p-2 rounded-lg shadow flex mt-3 items-center justify-center">
                        <div class="flex flex-col items-center">
                            <h4 class="text-gray-600 mb-2">Success Call-Out Rate</h4>
                            <div class="circular-progress-bar">
                                <svg class="progress-circle" width="120" height="120">
                                    <circle class="progress-circle-bg" cx="60" cy="60" r="50" fill="none"
                                        stroke-width="10" />
                                    <circle ref="progressCircleFill" class="progress-circle-fill" cx="60" cy="60" r="50"
                                        fill="none" stroke-width="10" />
                                </svg>
                                <span class="progress-text">{{ this.data.successRateData.toFixed(2) }}%</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid md:grid-cols-3 grid-cols-1 my-4 gap-4 w-full">

                <div class="rounded-md bg-[#A7704A] p-4 text-white">
                    <div class="flex items-center justify-between">
                        <div class="p-2">
                            <h3 class="text-sm font-medium my-2">Follow-ups Scheduled</h3>
                            <p class="text-2xl font-bold ">{{ data.followCountToday }}</p>
                        </div>
                        <svg width="65" height="68" viewBox="0 0 65 68" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M3.25 3V43.7953H62.25V3" stroke="white" stroke-width="5" stroke-linecap="round"
                                stroke-linejoin="round" />
                            <path d="M42.5837 55.2106L32.7503 65L22.917 55.2106" stroke="white" stroke-width="5"
                                stroke-linecap="round" stroke-linejoin="round" />
                            <path d="M32.75 43.7894V65" stroke="white" stroke-width="5" stroke-linecap="round"
                                stroke-linejoin="round" />
                            <path d="M39.2871 12.7544L47.5001 20.9474L39.2871 29.2524" stroke="white" stroke-width="5"
                                stroke-linecap="round" stroke-linejoin="round" />
                            <path d="M18.0015 20.949H47.5" stroke="white" stroke-width="5" stroke-linecap="round" />
                        </svg>

                    </div>
                </div>
                <div class="rounded-md bg-[#383838] p-4 text-white">
                    <div class="flex items-center justify-between">
                        <div class="p-2">
                            <h3 class="text-sm font-medium my-2">Successful Outcomes</h3>
                            <p class="text-2xl font-bold ">{{ data.successfulOutcomes }}</p>
                        </div>
                        <svg width="65" height="68" viewBox="0 0 65 68" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M32.5 3L40.647 8.9396L50.7326 8.921L53.8298 18.5124L62 24.421L58.8656 34L62 43.579L53.8298 49.4876L50.7326 59.079L40.647 59.0604L32.5 65L24.353 59.0604L14.2674 59.079L11.1702 49.4876L3 43.579L6.1344 34L3 24.421L11.1702 18.5124L14.2674 8.921L24.353 8.9396L32.5 3Z"
                                stroke="white" stroke-width="5" stroke-linecap="round" stroke-linejoin="round" />
                            <path d="M21.6431 34L29.3977 41.75L44.9068 26.25" stroke="white" stroke-width="5"
                                stroke-linecap="round" stroke-linejoin="round" />
                        </svg>


                    </div>
                </div>
                <div class="rounded-md bg-[#77A697] p-4 text-white">
                    <div class="flex items-center justify-between">
                        <div class="p-2">
                            <h3 class="text-sm font-medium my-2">Addresses Processed</h3>
                            <p class="text-2xl font-bold ">{{ data.addressProces }}</p>
                        </div>
                        <svg width="54" height="54" viewBox="0 0 54 54" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M13.5001 33.75C12.784 33.75 12.0972 34.0345 11.5909 34.5408C11.0845 35.0472 10.8001 35.7339 10.8001 36.45V41.2614C7.31954 37.3261 5.39885 32.2536 5.40007 27C5.39691 26.2059 5.4438 25.4123 5.54047 24.624C5.59199 24.2679 5.57178 23.905 5.48105 23.5568C5.39031 23.2085 5.23088 22.882 5.01211 22.5962C4.79335 22.3105 4.51967 22.0714 4.20717 21.8929C3.89466 21.7144 3.54964 21.6003 3.19237 21.5571C2.83511 21.5139 2.47281 21.5425 2.12678 21.6414C1.78075 21.7402 1.45797 21.9072 1.17741 22.1326C0.89685 22.3579 0.664172 22.6371 0.493056 22.9537C0.321939 23.2703 0.215836 23.6179 0.180983 23.976C0.057849 24.9793 -0.00256832 25.9893 8.36361e-05 27C0.0028078 33.4429 2.31857 39.6706 6.52597 44.55H2.70008C1.98399 44.55 1.29724 44.8344 0.790894 45.3408C0.284547 45.8471 8.36361e-05 46.5339 8.36361e-05 47.2499C8.36361e-05 47.966 0.284547 48.6528 0.790894 49.1591C1.29724 49.6655 1.98399 49.9499 2.70008 49.9499H13.5001C13.9224 49.9449 14.3374 49.8388 14.7103 49.6406C15.0833 49.4424 15.4034 49.1578 15.6438 48.8105L15.7437 48.6944C15.7626 48.6674 15.7626 48.635 15.7788 48.608C15.9063 48.3903 16.0027 48.1559 16.065 47.9114C16.105 47.7736 16.1339 47.6327 16.1514 47.4902C16.1595 47.4092 16.2 47.3363 16.2 47.2499V36.45C16.2 35.7339 15.9156 35.0472 15.4092 34.5408C14.9029 34.0345 14.2161 33.75 13.5001 33.75ZM17.55 10.8001H12.736C16.6719 7.31898 21.7455 5.39825 27 5.40013C27.7942 5.39786 28.5878 5.44565 29.376 5.54322C30.0874 5.62915 30.8039 5.42895 31.3677 4.98666C31.9315 4.54437 32.2965 3.89621 32.3825 3.18479C32.4684 2.47336 32.2682 1.75693 31.8259 1.19312C31.3836 0.629302 30.7354 0.264279 30.024 0.178349C29.0207 0.0561173 28.0107 -0.00339804 27 0.000149697C20.5572 0.00287386 14.3294 2.31863 9.45006 6.52602V2.70014C9.45006 1.98406 9.1656 1.2973 8.65925 0.790958C8.1529 0.284612 7.46615 0.000149697 6.75007 0.000149697C6.03399 0.000149697 5.34723 0.284612 4.84088 0.790958C4.33454 1.2973 4.05007 1.98406 4.05007 2.70014V13.5001C4.06759 13.6787 4.10287 13.855 4.15537 14.0266C4.22246 14.3591 4.35161 14.6761 4.53607 14.9608L4.56847 15.0067C4.74775 15.2647 4.9707 15.4895 5.22727 15.6709C5.25697 15.6925 5.27317 15.7249 5.30287 15.7465C5.34067 15.7735 5.38387 15.7816 5.42437 15.8032C5.57844 15.8947 5.74125 15.9707 5.91037 16.03C6.13784 16.1089 6.37484 16.157 6.61507 16.1731C6.66367 16.1731 6.70147 16.2001 6.75007 16.2001H17.55C18.2661 16.2001 18.9529 15.9156 19.4592 15.4093C19.9656 14.9029 20.25 14.2162 20.25 13.5001C20.25 12.784 19.9656 12.0973 19.4592 11.5909C18.9529 11.0846 18.2661 10.8001 17.55 10.8001ZM49.842 39.9708C49.7746 39.6392 49.6454 39.3232 49.4613 39.0393L49.4316 38.9934C49.2515 38.7351 49.0276 38.5103 48.7701 38.3292C48.7404 38.3076 48.7269 38.2752 48.6945 38.2536C48.6675 38.2347 48.6324 38.2347 48.6027 38.2158C48.2589 38.0274 47.8839 37.903 47.4957 37.8486C47.4093 37.8405 47.3364 37.8 47.25 37.8H36.45C35.7339 37.8 35.0472 38.0844 34.5408 38.5908C34.0345 39.0971 33.75 39.7839 33.75 40.5C33.75 41.2161 34.0345 41.9028 34.5408 42.4091C35.0472 42.9155 35.7339 43.2 36.45 43.2H41.2614C37.3261 46.6805 32.2536 48.6012 27 48.5999C26.2057 48.6013 25.4122 48.5526 24.624 48.4541C23.9126 48.3682 23.1962 48.5684 22.6324 49.0107C22.0685 49.453 21.7035 50.1012 21.6176 50.8126C21.5317 51.524 21.7319 52.2404 22.1742 52.8042C22.6164 53.3681 23.2646 53.7331 23.976 53.819C24.9793 53.9421 25.9892 54.0026 27 53.9999C33.4429 53.9972 39.6706 51.6814 44.55 47.474V51.2999C44.55 52.016 44.8344 52.7028 45.3408 53.2091C45.8471 53.7154 46.5339 53.9999 47.25 53.9999C47.9661 53.9999 48.6528 53.7154 49.1592 53.2091C49.6655 52.7028 49.95 52.016 49.95 51.2999V40.5C49.9316 40.3213 49.8954 40.1422 49.842 39.9708ZM51.3 9.45011C52.0161 9.45011 52.7028 9.16565 53.2092 8.6593C53.7155 8.15295 54 7.4662 54 6.75012C54 6.03404 53.7155 5.34729 53.2092 4.84094C52.7028 4.33459 52.0161 4.05013 51.3 4.05013H40.5C40.3242 4.06818 40.1505 4.10345 39.9816 4.15543L39.9627 4.15813C39.636 4.22631 39.3247 4.35447 39.0447 4.53613L38.9907 4.57123C38.7336 4.75075 38.5098 4.97368 38.3292 5.23003C38.3076 5.25703 38.2752 5.27323 38.2536 5.30293C38.2347 5.32993 38.2347 5.36503 38.2158 5.39473C38.0265 5.74096 37.9021 6.11886 37.8486 6.50982C37.8405 6.59082 37.8 6.66372 37.8 6.75012V17.5501C37.8 18.2662 38.0845 18.9529 38.5908 19.4593C39.0972 19.9656 39.7839 20.2501 40.5 20.2501C41.2161 20.2501 41.9028 19.9656 42.4092 19.4593C42.9155 18.9529 43.2 18.2662 43.2 17.5501V12.736C46.6789 16.6732 48.5994 21.746 48.6 27C48.6022 27.7942 48.5544 28.5878 48.4569 29.376C48.3707 30.0867 48.5704 30.8025 49.0119 31.366C49.4534 31.9296 50.1006 32.2947 50.8113 32.3811C50.9206 32.3941 51.0306 32.4004 51.1407 32.4C51.7998 32.3988 52.4358 32.1566 52.9286 31.7189C53.4215 31.2812 53.7373 30.6784 53.8164 30.024C53.9404 29.0208 54.0017 28.0109 54 27C53.9972 20.5572 51.6815 14.3295 47.4741 9.45011H51.3Z"
                                fill="white" />
                        </svg>


                    </div>
                </div>

            </div>
            <!-- User Productivity and Call Statistics Table -->
            <div class="bg-white p-4 rounded-lg shadow mt-6">
                <div class="border-b-2 p-1">
                    <span class="mx-4 font-extrabold ">
                        User Productivity and Call Statistics
                    </span>
                </div>
                <DataTable :value="userData" class="p-datatable-sm " paginator :rows="10"
                    :rowsPerPageOptions="[5, 10, 20, 50]" v-model:filters="filters"
                    :globalFilterFields="['user_name', 'total_logged_in_time', 'total_break_time', 'addresses_processed', 'average_processing_time', 'total_effective_working_time']">
                    <template #header>
                        <div class="md:flex hidden justify-end my-3">
                            <IconField>
                                <InputIcon>
                                    <i class="pi pi-search" />
                                </InputIcon>
                                <InputText v-model="filters.global.value" placeholder="Keyword Search" />
                            </IconField>
                        </div>
                    </template>
                    <Column field="user_name" header="User Name"></Column>
                    <Column field="total_logged_in_time" header="Logged-In Time">
                        <template #body="slotProps">
                            {{ formatSeconds(slotProps.data.total_logged_in_time) }}
                        </template>
                    </Column>
                    <Column field="total_break_time" header="Break Time">
                        <template #body="slotProps">
                            {{ formatSeconds(slotProps.data.total_break_time) }}
                        </template>
                    </Column>
                    <Column field="addresses_processed" header="Addresses Processed"></Column>
                    <Column field="average_processing_time" header="Average Processing Time">
                        <template #body="slotProps">
                            {{ formatSeconds(slotProps.data.average_processing_time) }}
                        </template>
                    </Column>
                    <Column field="total_effective_working_time" header="Total Effective Working Time">
                        <template #body="slotProps">
                            {{ formatSeconds(slotProps.data.total_effective_working_time) }}
                        </template>
                    </Column>
                </DataTable>
            </div>

            <div class="grid md:grid-cols-4 grid-cols-1 my-4 gap-4 w-full text-center">

                <div class="rounded-md bg-[#383838] p-4 text-white text-center">
                    <div class=" items-center ">
                        <div class="p-2">
                            <h3 class="text-sm font-medium my-2">Average Call Time:</h3>
                            <p class="text-2xl font-bold ">{{ formatSeconds(data.avgCall) }}</p>
                        </div>

                    </div>
                </div>
                <div class="rounded-md bg-[#383838] p-4 text-white">
                    <div class=" items-center ">
                        <div class="p-2">
                            <h3 class="text-sm font-medium my-2">Total Break Time</h3>
                            <p class="text-2xl font-bold ">{{ formatSeconds(data.totalBreak) }}</p>
                        </div>


                    </div>
                </div>
                <div class="rounded-md bg-[#383838] p-4 text-white">
                    <div class=" items-center ">
                        <div class="p-2">
                            <h3 class="text-sm font-medium my-2">Total Logged-In Time</h3>
                            <p class="text-2xl font-bold ">{{ formatSeconds(data.total_logged_in_time) }}</p>
                        </div>


                    </div>
                </div>
                <div class="rounded-md bg-[#383838] p-4 text-white">
                    <div class=" items-center ">
                        <div class="p-2">
                            <h3 class="text-sm font-medium my-2">Effective Productivity Rate</h3>
                            <p class="text-2xl font-bold ">{{ formatSeconds(data.total_logged_in_time -
                                    data.totalBreak) }}</p>
                        </div>


                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script>
import { FilterMatchMode } from '@primevue/core/api';

export default {
    props: {
        userData: Array,
        data: Object,
    },
    data() {
        return {
            filters: {
                global: { value: null, matchMode: FilterMatchMode.CONTAINS }
            },

            chartData: null,
            chartOptions: null,
            startDate: null,
            endDate: null,

        };
    },
    computed: {
        strokeDashoffset() {
            const progressValue = this.data.successRateData;
            const circleLength = 314;
            return circleLength - (progressValue / 100) * circleLength;
        }
    },

    mounted() {
        this.$nextTick(() => {
            const progressCircleFill = this.$refs.progressCircleFill
            progressCircleFill.style.strokeDashoffset = this.strokeDashoffset
        });
        console.log(this.data);

        this.initChartData();

    },
    methods: {
        onDateChange() {
            console.log('sad');

            // Only fetch data if both startDate and endDate have values
            if (this.startDate && this.endDate) {
                this.fetchData();
            }
        },
        fetchData() {
            // Perform an Inertia request to fetch data based on the date range
            this.$inertia.get(route('statistics.index'), { startDate: this.startDate, endDate: this.endDate }, {
                preserveState: true, // Preserve state to maintain form input values
                preserveScroll: true, // Preserve scroll position
                onSuccess: (page) => {
                    this.updateData(page.props);
                }
            });
        },
        initChartData() {
            // Initialize chart data and options when component is mounted
            this.chartData = this.setChartData();
            this.chartOptions = this.setChartOptions();
        },
        updateData(props) {
            console.log(props, 'sad');

            // Update local data based on Inertia response
            // this.$page.props.userData = this.$page.props.userData.merge(props.userData);
            // this.$page.props.data = this.$page.props.data.merge(props.data);
            // this.data = props.data;
            this.chartData = this.setChartData();
            const progressCircleFill = this.$refs.progressCircleFill
            progressCircleFill.style.strokeDashoffset = this.strokeDashoffset
        },
        setChartData() {
            const documentStyle = getComputedStyle(document.documentElement);
            const labels = Object.keys(this.data.dailyCallOutVolume);
            const data = Object.values(this.data.dailyCallOutVolume);
            return {

                labels,
                datasets: [
                    {
                        label: '',
                        data,
                        tension: 0.5,
                        // backgroundColor: 'rgba(68, 84, 195,0.1)',
                        borderColor: '#77A697',
                        borderWidth: 5,
                        pointStyle: 'circle',
                        pointRadius: 1,
                        pointHitRadius: 70,
                        pointBorderColor: 'transparent',
                        pointBackgroundColor: '#A7704A',
                    },

                ]

            };
        },
        formatSeconds(seconds) {
            if (seconds < 0) {
                seconds = 0;
            }
            const hours = Math.floor(seconds / 3600);
            const minutes = Math.floor((seconds % 3600) / 60);
            const remainingSeconds = Math.floor(seconds % 60); // Only using integer seconds for hh:mm:ss format

            // Helper function to pad numbers with leading zero if less than 10
            const pad = (num) => String(num).padStart(2, '0');

            return `${pad(hours)}:${pad(minutes)}:${pad(remainingSeconds)}`;
        },
        setChartOptions() {
            const fontFamily = 'Montserrat';
            const fontSize = 13;
            const fontColor = '#8e9cad';
            const gridLineColor = '#77A697';
            const zeroLineColor = '#000';

            return {
                responsive: true,
                maintainAspectRatio: false,
                tooltips: {
                    // mode: 'index',
                    titleFontSize: 12,
                    titleFontColor: '#77A697',
                    bodyFontColor: '#77A697',
                    backgroundColor: '#77A697',
                    bodyFontFamily: 'Montserrat',
                    cornerRadius: 0,
                    intersect: true,
                },
                plugins: {
                    legend: {
                        display: false,
                    }
                },

                scales: {
                    x: {
                        display: true,
                        ticks: {
                            display: true,
                            fontColor: "#77A697",
                            fontSize: "13",
                        },
                        scaleLabel: {
                            display: true,
                            labelString: 'Months',
                            fontSize: "15",
                            fontColor: "#77A697",
                        },
                        gridLines: {
                            display: true,
                            drawBorder: false,
                            color: '#77A697',
                            zeroLineColor: '#000'
                        }
                    },
                    y: {
                        display: true,
                        ticks: {
                            display: true,
                            fontColor,
                            fontSize,
                            maxRotation: 0,
                            stepSize: 2,
                            min: 0,
                            max: 500,
                        },
                        scaleLabel: {
                            display: true,
                            labelString: 'Revenue',
                            fontSize,
                            fontColor,
                        },
                        gridLines: {
                            display: true,
                            drawBorder: true,
                            color: gridLineColor,
                            zeroLineColor,
                        },
                    },
                },
            };
        }
    },
};
</script>


<style scoped>
.circular-progress-bar {
    position: relative;
    width: 120px;
    height: 120px;
}

.progress-circle {
    position: absolute;
    top: 0;
    left: 0;
}

.progress-circle-bg {
    stroke: #A7704A;
    stroke-width: 15;
}

.progress-circle-fill {
    stroke: #E4A57B;
    stroke-width: 15;
    stroke-dasharray: 314;
    stroke-dashoffset: 314;
    stroke-linecap: round;
    transition: stroke-dashoffset 0.5s ease-in-out;
}

.progress-text {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    font-size: 24px;
    font-weight: bold;
}

#app>div>main>div>div.grid.grid-cols-1.xl\:grid-cols-5.gap-4>div.grid.lg\:grid-cols-2.xl\:grid-cols-1>div.grid.grid-cols-1.sm\:grid-cols-3.border.my-3.mb-1.rounded-md.p-4.shadow.shadow-secondary {
    background: #fff;
    margin-top: 0;
    margin-bottom: 20px;
}

@media only screen and (max-width:768px) {
    #app>div>main>div>div.bg-white.p-4.rounded-lg.shadow.mt-6>div.p-datatable.p-component.p-datatable-sm>div.p-datatable-header>div>div {
        width: 100%;
    }

    #app>div>main>div>div.grid.md\:grid-cols-4.grid-cols-1.my-4.gap-4.w-full {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

@media only screen and (max-width:500px) {
    #app>div>main>div>div.grid.md\:grid-cols-4.grid-cols-1.my-4.gap-4.w-full {
        grid-template-columns: repeat(1, minmax(0, 1fr));
    }
}
</style>
