<template>
    <div class="pr-2">
        <div class="loading">
            <div class="arc"></div>
            <div class="arc"></div>
            <div class="arc"></div>
        </div>
    </div>
</template>

<style>
    .loading {
        position: relative;
        width: 1rem;
        height: 1rem;
        transform-style: preserve-3d;
        perspective: 800px;
    }
    .loading .arc {
        position: absolute;
        content: "";
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        border-radius: 50%;
        border-bottom: 3px solid red;
    }
    .loading .arc:nth-child(1) {
        animation: rotate1 1.15s linear infinite;
    }
    .loading .arc:nth-child(2) {
        animation: rotate2 1.15s linear infinite;
    }
    .loading .arc:nth-child(3) {
        animation: rotate3 1.15s linear infinite;
    }
    .loading .arc:nth-child(1) {
        animation-delay: -0.8s;
    }
    .loading .arc:nth-child(2) {
        animation-delay: -0.4s;
    }
    .loading .arc:nth-child(3) {
        animation-delay: 0s;
    }

    @keyframes rotate1 {
        from {
            transform: rotateX(35deg) rotateY(-45deg) rotateZ(0);
        }
        to {
            transform: rotateX(35deg) rotateY(-45deg) rotateZ(1turn);
        }
    }
    @keyframes rotate2 {
        from {
            transform: rotateX(50deg) rotateY(10deg) rotateZ(0);
        }
        to {
            transform: rotateX(50deg) rotateY(10deg) rotateZ(1turn);
        }
    }
    @keyframes rotate3 {
        from {
            transform: rotateX(35deg) rotateY(55deg) rotateZ(0);
        }
        to {
            transform: rotateX(35deg) rotateY(55deg) rotateZ(1turn);
        }
    }
</style>
