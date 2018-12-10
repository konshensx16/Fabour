let myAudio = require('./notification.mp3')

const playSound = () => {
    let audio = new Audio(myAudio)
    let audioPromise = audio.play()
    audioPromise.then((res) => {
        console.log('playing audio...')
    })
        .catch((err) => {
            console.log(err)
        })
}

export { playSound }