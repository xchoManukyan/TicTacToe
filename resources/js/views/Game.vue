<template>
    <v-container class="py-12">
        <div class="d-flex align-center justify-center">
            <div class="elevation-10 game-table">
                <v-container fluid>
                    <v-row no-gutters>
                        <v-col cols="12" md="3"></v-col>
                        <v-col cols="12" md="6">
                            <div class="display-1 title-box">
                                {{ title }}
                            </div>
                        </v-col>
                        <v-col cols="12" md="3">
                            <v-select
                                dark
                                color="#ffffff"
                                background-color="#1a202c"
                                v-model="boxesCount"
                                :items="boxesCounts"
                                item-text="text"
                                item-value="value"
                                menu-props="auto"
                                hide-details
                                label="Boxes count"
                                single-line
                            >
                                <template v-slot:selection="{ item }">
                                    <span style="color: #ffffff; font-size: 36px">{{ item.text }}</span>
                                </template>
                            </v-select>
                        </v-col>
                    </v-row>
                </v-container>
                <div class="game-box">
                    <div class="d-flex" v-for="x in boxesCount">
                        <div
                            v-for="y in boxesCount"
                            @click="!pause && !codeQueue? makeStep(x, y): null"
                            class="step-box"
                            :class="[boxAvailableClass(x, y), boxBorderClass(x, y)]"
                        >
                            <template v-if="isStepIsset(x, y)">
                                <span class="animate__animated animate__zoomIn">{{ isStepIsset(x, y).user? 'x': 'o' }}</span>
                            </template>
                            <template v-if="winner">
                                <div class="win-line" v-if="checkWinStep(x, y) && winner.type === 'row'"></div>
                                <div class="win-line wl-col" v-if="checkWinStep(x, y) && winner.type === 'col'"></div>
                                <div class="win-line wl-diagonal-1" v-if="checkWinStep(x, y) && winner.type === 'diagonal1'"></div>
                                <div class="win-line wl-diagonal-2" v-if="checkWinStep(x, y) && winner.type === 'diagonal2'"></div>
                            </template>
                        </div>
                    </div>
                </div>
                <div class="d-flex justify-center" style="padding: 10px;">
                    <v-btn v-if="!ended" class="mx-2" small dark color="#C2185B" @click="started? pause = !pause: startRequest()">
                        {{ pause? 'Start': 'Pause' }}
                    </v-btn>
                    <v-btn v-if="!pause || ended" class="mx-2" small color="#FFFFFF" @click="restart()">Restart</v-btn>
                </div>
            </div>
        </div>
    </v-container>
</template>

<script>
export default {
    props: ['user'],
    name: "Game",
    data(){
      return {
          gameId: null,
          codeQueue: true,
          pause: true,
          steps: [],
          boxesCount: 3,
          boxesCounts: [
              {text: '3x3', value: 3},
              {text: '4x4', value: 4},
              {text: '5x5', value: 5},
              ],
          winner: null,

          started: false,
          ended: false,
          draw: false,
      }
    },
    computed: {
        availableSteps(){
            let result = []
            for (let y = 1; y <= this.boxesCount; y++) {
                for (let x = 1; x <= this.boxesCount; x++) {
                    if(!this.isStepIsset(x, y)){
                        result.push({x: x, y: y})
                    }
                }
            }
            return result;
        },
        title(){
            if (this.winner){
                return this.winner.user? 'You won!': 'You lose!';
            }else if(this.draw){
                return "It's draw!"
            }else {
                return 'Tic Tac Toe'
            }
        }
    },
    watch: {
        pause(){
            if (!this.ended){
                this.makeCodeStep()
            }
        },
        codeQueue(){
            if (!this.ended){
                this.makeCodeStep()
            }
        },
        steps(){
            if (!this.winner && this.steps.length === this.boxesCount * this.boxesCount){
                this.draw = true;
                this.ended = true;
                this.started = false;
                this.endRequest()
            }
        },
        boxesCount(){
            this.reset()
        }
    },
    methods: {

        /*styling*/

        boxAvailableClass(x, y){
            return !this.pause && !this.ended && !this.codeQueue && !this.isStepIsset(x, y)? 'available': null;
        },

        boxBorderClass(x, y){
            let border = null;
            if (y < this.boxesCount && x < this.boxesCount){
                border = 'border-rb'
            }else if (y < this.boxesCount && x === this.boxesCount){
                border = 'border-r'
            }else if (y === this.boxesCount && x < this.boxesCount){
                border = 'border-b'
            }
            return border;
        },

        /*step*/

        makeStep(x, y){
            if(!this.codeQueue && !this.pause && !this.ended && this.availableSteps.length){
                this.steps.push({
                    x: x,
                    y: y,
                    user: true,
                })
                !this.checkWin(true)? this.codeQueue = true: null;
                this.stepRequest(x, y);
            }
        },

        makeCodeStep(){
            if(this.codeQueue && !this.pause && !this.ended && this.availableSteps.length){
                let stepCoords = this.availableSteps[Math.floor(Math.random() * this.availableSteps.length)]

                this.steps.push({
                    x: stepCoords.x,
                    y: stepCoords.y,
                    user: false,
                })
                this.stepRequest(stepCoords.x, stepCoords.y);
                !this.checkWin(false)? this.codeQueue = false: null;
            }
        },

        isStepIsset(x, y){
            return this.steps.find(item => item.x === x && item.y === y);
        },

        /*win*/

        checkWinStep(x, y){
            if (this.winner){
                return this.winner.steps.find(item => item.x === x && item.y === y);
            }
        },

        checkWin(user){
            let steps = this.steps.filter(item => item.user === user)

            let winValues = this.getWinValues(steps);

            if (winValues){
                this.winner = {user: user, steps: winValues.steps, type: winValues.type}
                this.ended = true;
                this.endRequest()
                return true;
            }
        },

        colWin(steps){
            for (let y = 1; y <= this.boxesCount; y++) {
                let win = true;
                let winSteps = [];
                for (let x = 1; x <= this.boxesCount; x++) {
                    steps.find(item => item.x === x && item.y === y)?
                        winSteps.push({x: x, y: y}):
                        win = false;
                }
                if(win){
                    return winSteps;
                }
            }
        },

        rowWin(steps){
            for (let x = 1; x <= this.boxesCount; x++) {
                let win = true;
                let winSteps = [];
                for (let y = 1; y <= this.boxesCount; y++) {
                    steps.find(item => item.x === x && item.y === y)?
                        winSteps.push({x: x, y: y}):
                        win = false;
                }
                if(win){
                    return winSteps;
                }
            }
        },

        diagonal1Win(steps){
            let win = true;
            let winSteps = [];
            for (let d = 1; d <= this.boxesCount; d++) {
                steps.find(item => item.x === d && item.y === d)?
                    winSteps.push({x: d, y: d}):
                    win = false;
            }
            if(win){
                return winSteps;
            }
        },

        diagonal2Win(steps){
            let win = true;
            let winSteps = [];
            for (let d = 1; d <= this.boxesCount; d++) {
                steps.find(item => item.x === this.boxesCount - (d - 1) && item.y === d)?
                    winSteps.push({x: this.boxesCount - (d - 1), y: d}):
                    win = false;
            }
            if(win){
                return winSteps;
            }
        },

        getWinValues(steps){

            /*col win*/
            let winSteps = this.colWin(steps)
            if (winSteps){
                return {steps: winSteps, type: 'col'}
            }

            /*row win*/
            winSteps = this.rowWin(steps)
            if (winSteps){
                return {steps: winSteps, type: 'row'}
            }

            /*diagonal 1 win*/
            winSteps = this.diagonal1Win(steps)
            if (winSteps){
                return {steps: winSteps, type: 'diagonal1'}
            }

            /*diagonal 2 win*/
            winSteps = this.diagonal2Win(steps)
            if (winSteps){
                return {steps: winSteps, type: 'diagonal2'}
            }

            return false;
        },

        /*reset values*/

        reset(){
            this.codeQueue = true;
            this.pause = true;
            this.winner = null;
            this.ended = false;
            this.draw = false;
            this.started = false;
            this.steps = [];
            this.gameId = null;
        },

        restart(){
            this.codeQueue = true;
            this.winner = null;
            this.ended = false;
            this.draw = false;
            this.steps = [];
            this.pause = true;
            this.started = false;
            this.gameId = null;
            this.startRequest();
        },

        /*requests*/

        startRequest(){
            axios.post('axios/start', { boxes_count: this.boxesCount })
                .then(response => {
                    this.started = true;
                    this.pause = false;
                    this.gameId = response.data.game_id
                })
                .catch(error => {
                    console.log(error.response.data.message)
                })
        },

        stepRequest(x, y){
            axios.post('axios/step', {x: x, y: y, game_id: this.gameId})
                .then()
                .catch(error => {
                    console.log(error.response.data.message)
                })
        },

        endRequest(){
            let data = {
                game_id: this.gameId,
                wined: this.winner? this.winner.user: false,
                draw: this.draw
            }

            axios.post('axios/end', data)
                .then()
                .catch(error => {
                    console.log(error.response.data.message)
                })
        }

    },
    created() {

    }
}
</script>
<style>

.game-table{
    background-color: #1a202c
}

.game-box{
    padding: 20px;
}

.title-box{
    color: #ffffff;
    padding: 10px;
    text-align: center;
}

.step-box{
    position: relative;
    font-size: 6rem !important;
    font-weight: 300;
    line-height: 6rem;
    letter-spacing: -0.015625em !important;
    font-family: "Roboto", sans-serif !important;
    color: #ffff;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 150px;
    width: 150px;
}

.available{
    cursor: pointer;
}

.available:hover{
    transition: 0.3s;
    background-color: #4a5568;
}

.border-rb{
    border-right: 2px solid #C2185B;
    border-bottom: 2px solid #C2185B;
}

.border-b{
    border-bottom: 2px solid #C2185B;
}

.border-r{
    border-right: 2px solid #C2185B;
}

.win-line{
    position: absolute;
    top: 50%;
    left: 0;
    width: 100%;
    height: 1px;
    z-index: 5;
    background: white;
}
.wl-col{
    transform: rotate(90deg);
}

.wl-diagonal-1{
    transform: rotate(45deg);
}

.wl-diagonal-2{
    transform: rotate(135deg);
}

</style>
